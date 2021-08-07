<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Action;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Sonata\AdminBundle\Search\ChainableFilterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class RetrieveAutocompleteItemsAction
{
    /**
     * @var AdminFetcherInterface
     */
    private $adminFetcher;

    public function __construct(AdminFetcherInterface $adminFetcher)
    {
        $this->adminFetcher = $adminFetcher;
    }

    /**
     * Retrieve list of items for autocomplete form field.
     *
     * @throws \RuntimeException
     * @throws AccessDeniedException
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $admin = $this->adminFetcher->get($request);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException(sprintf(
                'Could not find admin for code "%s".',
                $request->get('_sonata_admin')
            ));
        }

        $context = $request->get('_context', '');
        if ('filter' === $context) {
            $admin->checkAccess('list');
        } elseif (!$admin->hasAccess('create') && !$admin->hasAccess('edit')) {
            throw new AccessDeniedException();
        }

        // subject will be empty to avoid unnecessary database requests and keep autocomplete function fast
        $admin->setSubject($admin->getNewInstance());

        if ('filter' === $context) {
            // filter
            $fieldDescription = $this->retrieveFilterFieldDescription($admin, $request->get('field'));
            $filterAutocomplete = $admin->getDatagrid()->getFilter($fieldDescription->getName());

            $property = $filterAutocomplete->getFieldOption('property');
            $callback = $filterAutocomplete->getFieldOption('callback');
            $minimumInputLength = $filterAutocomplete->getFieldOption('minimum_input_length', 3);
            $itemsPerPage = $filterAutocomplete->getFieldOption('items_per_page', 10);
            $reqParamPageNumber = $filterAutocomplete->getFieldOption('req_param_name_page_number', DatagridInterface::PAGE);
            $toStringCallback = $filterAutocomplete->getFieldOption('to_string_callback');
            $targetAdminAccessAction = $filterAutocomplete->getFieldOption('target_admin_access_action', 'list');
            $responseItemCallback = $filterAutocomplete->getFieldOption('response_item_callback');
        } else {
            // create/edit form
            $fieldDescription = $this->retrieveFormFieldDescription($admin, $request->get('field'));
            $formAutocomplete = $admin->getForm()->get($fieldDescription->getName());

            $formAutocompleteConfig = $formAutocomplete->getConfig();
            if (true === $formAutocompleteConfig->getAttribute('disabled')) {
                throw new AccessDeniedException(
                    'Autocomplete list can`t be retrieved because the form element is disabled or read_only.'
                );
            }

            $property = $formAutocompleteConfig->getAttribute('property');
            $callback = $formAutocompleteConfig->getAttribute('callback');
            $minimumInputLength = $formAutocompleteConfig->getAttribute('minimum_input_length');
            $itemsPerPage = $formAutocompleteConfig->getAttribute('items_per_page');
            $reqParamPageNumber = $formAutocompleteConfig->getAttribute('req_param_name_page_number');
            $toStringCallback = $formAutocompleteConfig->getAttribute('to_string_callback');
            $targetAdminAccessAction = $formAutocompleteConfig->getAttribute('target_admin_access_action');
            $responseItemCallback = $formAutocompleteConfig->getAttribute('response_item_callback');
        }

        $searchText = $request->get('q', '');

        $targetAdmin = $fieldDescription->getAssociationAdmin();

        // check user permission
        $targetAdmin->checkAccess($targetAdminAccessAction);

        if (mb_strlen($searchText, 'UTF-8') < $minimumInputLength) {
            return new JsonResponse(['status' => 'KO', 'message' => 'Too short search string.'], Response::HTTP_FORBIDDEN);
        }

        $targetAdmin->setFilterPersister(null);
        $datagrid = $targetAdmin->getDatagrid();

        if (null !== $callback) {
            if (!\is_callable($callback)) {
                throw new \RuntimeException('Callback does not contain callable function.');
            }

            $callback($targetAdmin, $property, $searchText);
        } elseif (\is_array($property)) {
            $previousFilter = null;
            foreach ($property as $prop) {
                if (!$datagrid->hasFilter($prop)) {
                    throw new \RuntimeException(sprintf(
                        'To retrieve autocomplete items, you MUST add the filter "%s"'
                        .' to the %s::configureDatagridFilters() method.',
                        $prop,
                        \get_class($targetAdmin)
                    ));
                }

                $filter = $datagrid->getFilter($prop);
                if (!$filter instanceof ChainableFilterInterface) {
                    throw new \RuntimeException(sprintf(
                        'To retrieve autocomplete items with multiple properties,'
                        .' the filter "%s" of the admin "%s" MUST implements "%s".',
                        $filter->getName(),
                        \get_class($targetAdmin),
                        ChainableFilterInterface::class
                    ));
                }

                $filter->setCondition(FilterInterface::CONDITION_OR);
                if (null !== $previousFilter) {
                    $filter->setPreviousFilter($previousFilter);
                }

                $datagrid->setValue($filter->getFormName(), null, $searchText);

                $previousFilter = $filter;
            }
        } else {
            if (!$datagrid->hasFilter($property)) {
                throw new \RuntimeException(sprintf(
                    'To retrieve autocomplete items, you MUST add the filter "%s"'
                    .' to the %s::configureDatagridFilters() method.',
                    $property,
                    \get_class($targetAdmin)
                ));
            }

            $datagrid->setValue($datagrid->getFilter($property)->getFormName(), null, $searchText);
        }

        $datagrid->setValue(DatagridInterface::PER_PAGE, null, $itemsPerPage);
        $datagrid->setValue(DatagridInterface::PAGE, null, $request->query->get($reqParamPageNumber, '1'));
        $datagrid->buildPager();

        $pager = $datagrid->getPager();

        $items = [];
        $results = $pager->getCurrentPageResults();

        foreach ($results as $model) {
            if (null !== $toStringCallback) {
                if (!\is_callable($toStringCallback)) {
                    throw new \RuntimeException('Option "to_string_callback" does not contain callable function.');
                }

                $label = $toStringCallback($model, $property);
            } else {
                $resultMetadata = $targetAdmin->getObjectMetadata($model);
                $label = $resultMetadata->getTitle();
            }

            $item = [
                'id' => $admin->id($model),
                'label' => $label,
            ];

            if (\is_callable($responseItemCallback)) {
                $item = \call_user_func($responseItemCallback, $admin, $model, $item);
            }

            $items[] = $item;
        }

        return new JsonResponse([
            'status' => 'OK',
            'more' => \count($items) > 0 && !$pager->isLastPage(),
            'items' => $items,
        ]);
    }

    /**
     * Retrieve the filter field description given by field name.
     *
     * @throws \RuntimeException
     *
     * @phpstan-template T of object
     * @phpstan-param AdminInterface<T> $admin
     */
    private function retrieveFilterFieldDescription(
        AdminInterface $admin,
        string $field
    ): FieldDescriptionInterface {
        if (!$admin->hasFilterFieldDescription($field)) {
            throw new \RuntimeException(sprintf('The field "%s" does not exist.', $field));
        }

        $fieldDescription = $admin->getFilterFieldDescription($field);

        if (null === $fieldDescription->getTargetModel()) {
            throw new \RuntimeException(sprintf('No associated entity with field "%s".', $field));
        }

        return $fieldDescription;
    }

    /**
     * Retrieve the form field description given by field name.
     *
     * @param AdminInterface<object> $admin
     *
     * @throws \RuntimeException
     */
    private function retrieveFormFieldDescription(
        AdminInterface $admin,
        string $field
    ): FieldDescriptionInterface {
        if (!$admin->hasFormFieldDescription($field)) {
            throw new \RuntimeException(sprintf('The field "%s" does not exist.', $field));
        }

        $fieldDescription = $admin->getFormFieldDescription($field);

        if (null === $fieldDescription->getTargetModel()) {
            throw new \RuntimeException(sprintf('No associated entity with field "%s".', $field));
        }

        return $fieldDescription;
    }
}
