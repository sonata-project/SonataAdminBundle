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
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Request\AdminFetcher;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\Form\Form;
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

    /**
     * @param Pool|AdminFetcherInterface $poolOrAdminFetcher
     */
    public function __construct($poolOrAdminFetcher)
    {
        if ($poolOrAdminFetcher instanceof AdminFetcherInterface) {
            $this->adminFetcher = $poolOrAdminFetcher;
        } elseif ($poolOrAdminFetcher instanceof Pool) {
            @trigger_error(sprintf(
                'Passing other type than %s in argument 2 to %s() is deprecated since sonata-project/admin-bundle 3.100'
                .' and will throw %s error in 4.0.',
                AdminFetcherInterface::class,
                __METHOD__,
                \TypeError::class
            ), \E_USER_DEPRECATED);

            $this->adminFetcher = new AdminFetcher($poolOrAdminFetcher);
        } else {
            throw new \TypeError(sprintf(
                'Argument 2 passed to "%s()" must be either an instance of %s or %s, %s given.',
                __METHOD__,
                Pool::class,
                AdminFetcherInterface::class,
                \is_object($poolOrAdminFetcher) ? 'instance of "'.\get_class($poolOrAdminFetcher).'"' : '"'.\gettype($poolOrAdminFetcher).'"'
            ));
        }
    }

    /**
     * Retrieve list of items for autocomplete form field.
     *
     * @throws \RuntimeException
     * @throws AccessDeniedException
     */
    public function __invoke(Request $request): JsonResponse
    {
        // NEXT_MAJOR: Remove this BC-layer.
        if (null === $request->get('_sonata_admin')) {
            @trigger_error(
                'Not passing the "_sonata_admin" parameter in the request is deprecated since sonata-project/admin-bundle 3.100'
                .' and will throw an exception in 4.0.',
                \E_USER_DEPRECATED
            );

            $request->query->set('_sonata_admin', $request->get('admin_code'));
        }

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
            if ($formAutocompleteConfig->getAttribute('disabled')) {
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
        } else {
            if (\is_array($property)) {
                // multiple properties
                foreach ($property as $prop) {
                    if (!$datagrid->hasFilter($prop)) {
                        throw new \RuntimeException(sprintf(
                            'To retrieve autocomplete items,'
                            .' you should add filter "%s" to "%s" in configureDatagridFilters() method.',
                            $prop,
                            \get_class($targetAdmin)
                        ));
                    }

                    $filter = $datagrid->getFilter($prop);
                    $filter->setCondition(FilterInterface::CONDITION_OR);

                    $datagrid->setValue($filter->getFormName(), null, $searchText);
                }
            } else {
                if (!$datagrid->hasFilter($property)) {
                    throw new \RuntimeException(sprintf(
                        'To retrieve autocomplete items,'
                        .' you should add filter "%s" to "%s" in configureDatagridFilters() method.',
                        $property,
                        \get_class($targetAdmin)
                    ));
                }

                $datagrid->setValue($datagrid->getFilter($property)->getFormName(), null, $searchText);
            }
        }

        $datagrid->setValue(DatagridInterface::PER_PAGE, null, $itemsPerPage);
        $datagrid->setValue(DatagridInterface::PAGE, null, $request->query->get($reqParamPageNumber, '1'));
        $datagrid->buildPager();

        $pager = $datagrid->getPager();

        $items = [];
        // NEXT_MAJOR: remove the existence check and just use $pager->getCurrentPageResults()
        if (method_exists($pager, 'getCurrentPageResults')) {
            $results = $pager->getCurrentPageResults();
        } else {
            @trigger_error(sprintf(
                'Not implementing "%s::getCurrentPageResults()" is deprecated since sonata-project/admin-bundle 3.87 and will fail in 4.0.',
                PagerInterface::class
            ), \E_USER_DEPRECATED);

            $results = $pager->getResults();
        }

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
                \call_user_func($responseItemCallback, $admin, $model, $item);
            }

            $items[] = $item;
        }

        return new JsonResponse([
            'status' => 'OK',
            'more' => !$pager->isLastPage(),
            'items' => $items,
        ]);
    }

    /**
     * Retrieve the filter field description given by field name.
     *
     * @throws \RuntimeException
     */
    private function retrieveFilterFieldDescription(
        AdminInterface $admin,
        string $field
    ): FieldDescriptionInterface {
        $fieldDescription = $admin->getFilterFieldDescription($field);

        if (!$fieldDescription) {
            throw new \RuntimeException(sprintf('The field "%s" does not exist.', $field));
        }

        // NEXT_MAJOR: Remove the check and use `getTargetModel`.
        if (method_exists($fieldDescription, 'getTargetModel')) {
            $targetModel = $fieldDescription->getTargetModel();
        } else {
            $targetModel = $fieldDescription->getTargetEntity();
        }

        if (null === $targetModel) {
            throw new \RuntimeException(sprintf('No associated entity with field "%s".', $field));
        }

        return $fieldDescription;
    }

    /**
     * Retrieve the form field description given by field name.
     *
     * @throws \RuntimeException
     */
    private function retrieveFormFieldDescription(
        AdminInterface $admin,
        string $field
    ): FieldDescriptionInterface {
        $fieldDescription = $admin->getFormFieldDescription($field);

        if (!$fieldDescription) {
            throw new \RuntimeException(sprintf('The field "%s" does not exist.', $field));
        }

        // NEXT_MAJOR: Remove the check and use `getTargetModel`.
        if (method_exists($fieldDescription, 'getTargetModel')) {
            $targetModel = $fieldDescription->getTargetModel();
        } else {
            $targetModel = $fieldDescription->getTargetEntity();
        }

        if (null === $targetModel) {
            throw new \RuntimeException(sprintf('No associated entity with field "%s".', $field));
        }

        return $fieldDescription;
    }
}
