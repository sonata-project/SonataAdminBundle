<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Controller;

use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Twig\Extension\SonataAdminExtension;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bridge\Twig\Command\DebugCommand;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class HelperController
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var AdminHelper
     */
    protected $helper;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @param ValidatorInterface $validator
     */
    public function __construct(Environment $twig, Pool $pool, AdminHelper $helper, $validator)
    {
        // NEXT_MAJOR: Move ValidatorInterface check to method signature
        if (!($validator instanceof ValidatorInterface)) {
            throw new \InvalidArgumentException(
                'Argument 4 is an instance of '.get_class($validator).', expecting an instance of'
                .' \Symfony\Component\Validator\Validator\ValidatorInterface'
            );
        }

        $this->twig = $twig;
        $this->pool = $pool;
        $this->helper = $helper;
        $this->validator = $validator;
    }

    /**
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function appendFormFieldElementAction(Request $request)
    {
        $code = $request->get('code');
        $elementId = $request->get('elementId');
        $objectId = $request->get('objectId');
        $uniqid = $request->get('uniqid');

        $admin = $this->pool->getInstance($code);
        $admin->setRequest($request);

        if ($uniqid) {
            $admin->setUniqid($uniqid);
        }

        $subject = $admin->getModelManager()->find($admin->getClass(), $objectId);
        if ($objectId && !$subject) {
            throw new NotFoundHttpException();
        }

        if (!$subject) {
            $subject = $admin->getNewInstance();
        }

        $admin->setSubject($subject);

        list(, $form) = $this->helper->appendFormFieldElement($admin, $subject, $elementId);

        /* @var $form \Symfony\Component\Form\Form */
        $view = $this->helper->getChildFormView($form->createView(), $elementId);

        // render the widget
        $renderer = $this->getFormRenderer();
        $renderer->setTheme($view, $admin->getFormTheme());

        return new Response($renderer->searchAndRenderBlock($view, 'widget'));
    }

    /**
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function retrieveFormFieldElementAction(Request $request)
    {
        $code = $request->get('code');
        $elementId = $request->get('elementId');
        $objectId = $request->get('objectId');
        $uniqid = $request->get('uniqid');

        $admin = $this->pool->getInstance($code);
        $admin->setRequest($request);

        if ($uniqid) {
            $admin->setUniqid($uniqid);
        }

        if ($objectId) {
            $subject = $admin->getModelManager()->find($admin->getClass(), $objectId);
            if (!$subject) {
                throw new NotFoundHttpException(
                    sprintf('Unable to find the object id: %s, class: %s', $objectId, $admin->getClass())
                );
            }
        } else {
            $subject = $admin->getNewInstance();
        }

        $admin->setSubject($subject);

        $formBuilder = $admin->getFormBuilder();

        $form = $formBuilder->getForm();
        $form->setData($subject);
        $form->handleRequest($request);

        $view = $this->helper->getChildFormView($form->createView(), $elementId);

        // render the widget
        $renderer = $this->getFormRenderer();
        $renderer->setTheme($view, $admin->getFormTheme());

        return new Response($renderer->searchAndRenderBlock($view, 'widget'));
    }

    /**
     * @throws NotFoundHttpException|\RuntimeException
     *
     * @return Response
     */
    public function getShortObjectDescriptionAction(Request $request)
    {
        $code = $request->get('code');
        $objectId = $request->get('objectId');
        $uniqid = $request->get('uniqid');
        $linkParameters = $request->get('linkParameters', []);

        $admin = $this->pool->getInstance($code);

        if (!$admin) {
            throw new NotFoundHttpException();
        }

        $admin->setRequest($request);

        if ($uniqid) {
            $admin->setUniqid($uniqid);
        }

        if (!$objectId) {
            $objectId = null;
        }

        $object = $admin->getObject($objectId);

        if (!$object && 'html' == $request->get('_format')) {
            return new Response();
        }

        if ('json' == $request->get('_format')) {
            return new JsonResponse(['result' => [
                'id' => $admin->id($object),
                'label' => $admin->toString($object),
            ]]);
        } elseif ('html' == $request->get('_format')) {
            return new Response($this->twig->render($admin->getTemplate('short_object_description'), [
                'admin' => $admin,
                'description' => $admin->toString($object),
                'object' => $object,
                'link_parameters' => $linkParameters,
            ]));
        }

        throw new \RuntimeException('Invalid format');
    }

    /**
     * @return Response
     */
    public function setObjectFieldValueAction(Request $request)
    {
        $field = $request->get('field');
        $code = $request->get('code');
        $objectId = $request->get('objectId');
        $value = $originalValue = $request->get('value');
        $context = $request->get('context');

        $admin = $this->pool->getInstance($code);
        $admin->setRequest($request);

        // alter should be done by using a post method
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse('Expected an XmlHttpRequest request header', 405);
        }

        if ('POST' != $request->getMethod()) {
            return new JsonResponse('Expected a POST Request', 405);
        }

        $rootObject = $object = $admin->getObject($objectId);

        if (!$object) {
            return new JsonResponse('Object does not exist', 404);
        }

        // check user permission
        if (false === $admin->hasAccess('edit', $object)) {
            return new JsonResponse('Invalid permissions', 403);
        }

        if ('list' == $context) {
            $fieldDescription = $admin->getListFieldDescription($field);
        } else {
            return new JsonResponse('Invalid context', 400);
        }

        if (!$fieldDescription) {
            return new JsonResponse('The field does not exist', 400);
        }

        if (!$fieldDescription->getOption('editable')) {
            return new JsonResponse('The field cannot be edited, editable option must be set to true', 400);
        }

        $propertyPath = new PropertyPath($field);

        // If property path has more than 1 element, take the last object in order to validate it
        if ($propertyPath->getLength() > 1) {
            $object = $this->pool->getPropertyAccessor()->getValue($object, $propertyPath->getParent());

            $elements = $propertyPath->getElements();
            $field = end($elements);
            $propertyPath = new PropertyPath($field);
        }

        // Handle date type has setter expect a DateTime object
        if ('' !== $value && 'date' == $fieldDescription->getType()) {
            $value = new \DateTime($value);
        }

        // Handle boolean type transforming the value into a boolean
        if ('' !== $value && 'boolean' == $fieldDescription->getType()) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        // Handle entity choice association type, transforming the value into entity
        if ('' !== $value
            && 'choice' == $fieldDescription->getType()
            && null !== $fieldDescription->getOption('class')
            && $fieldDescription->getOption('class') === $fieldDescription->getTargetEntity()
        ) {
            $value = $admin->getModelManager()->find($fieldDescription->getOption('class'), $value);

            if (!$value) {
                return new JsonResponse(sprintf(
                    'Edit failed, object with id: %s not found in association: %s.',
                    $originalValue,
                    $field
                ), 404);
            }
        }

        $this->pool->getPropertyAccessor()->setValue($object, $propertyPath, '' !== $value ? $value : null);

        $violations = $this->validator->validate($object);

        if (count($violations)) {
            $messages = [];

            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }

            return new JsonResponse(implode("\n", $messages), 400);
        }

        $admin->update($object);

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...
        $extension = $this->twig->getExtension(SonataAdminExtension::class);

        $content = $extension->renderListElement($this->twig, $rootObject, $fieldDescription);

        return new JsonResponse($content, 200);
    }

    /**
     * Retrieve list of items for autocomplete form field.
     *
     * @throws \RuntimeException
     * @throws AccessDeniedException
     *
     * @return JsonResponse
     */
    public function retrieveAutocompleteItemsAction(Request $request)
    {
        $admin = $this->pool->getInstance($request->get('admin_code'));
        $admin->setRequest($request);
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
            $reqParamPageNumber = $filterAutocomplete->getFieldOption('req_param_name_page_number', '_page');
            $toStringCallback = $filterAutocomplete->getFieldOption('to_string_callback');
            $targetAdminAccessAction = $filterAutocomplete->getFieldOption('target_admin_access_action', 'list');
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
        }

        $searchText = $request->get('q');

        $targetAdmin = $fieldDescription->getAssociationAdmin();

        // check user permission
        $targetAdmin->checkAccess($targetAdminAccessAction);

        if (mb_strlen($searchText, 'UTF-8') < $minimumInputLength) {
            return new JsonResponse(['status' => 'KO', 'message' => 'Too short search string.'], 403);
        }

        $targetAdmin->setFilterPersister(null);
        $datagrid = $targetAdmin->getDatagrid();

        if (null !== $callback) {
            if (!is_callable($callback)) {
                throw new \RuntimeException('Callback does not contain callable function.');
            }

            call_user_func($callback, $targetAdmin, $property, $searchText);
        } else {
            if (is_array($property)) {
                // multiple properties
                foreach ($property as $prop) {
                    if (!$datagrid->hasFilter($prop)) {
                        throw new \RuntimeException(sprintf(
                            'To retrieve autocomplete items,'
                            .' you should add filter "%s" to "%s" in configureDatagridFilters() method.',
                            $prop, get_class($targetAdmin)
                        ));
                    }

                    $filter = $datagrid->getFilter($prop);
                    $filter->setCondition(FilterInterface::CONDITION_OR);

                    $datagrid->setValue($prop, null, $searchText);
                }
            } else {
                if (!$datagrid->hasFilter($property)) {
                    throw new \RuntimeException(sprintf(
                        'To retrieve autocomplete items,'
                        .' you should add filter "%s" to "%s" in configureDatagridFilters() method.',
                        $property,
                        get_class($targetAdmin)
                    ));
                }

                $datagrid->setValue($property, null, $searchText);
            }
        }

        $datagrid->setValue('_per_page', null, $itemsPerPage);
        $datagrid->setValue('_page', null, $request->query->get($reqParamPageNumber, 1));
        $datagrid->buildPager();

        $pager = $datagrid->getPager();

        $items = [];
        $results = $pager->getResults();

        foreach ($results as $entity) {
            if (null !== $toStringCallback) {
                if (!is_callable($toStringCallback)) {
                    throw new \RuntimeException('Option "to_string_callback" does not contain callable function.');
                }

                $label = call_user_func($toStringCallback, $entity, $property);
            } else {
                $resultMetadata = $targetAdmin->getObjectMetadata($entity);
                $label = $resultMetadata->getTitle();
            }

            $items[] = [
                'id' => $admin->id($entity),
                'label' => $label,
            ];
        }

        return new JsonResponse([
            'status' => 'OK',
            'more' => !$pager->isLastPage(),
            'items' => $items,
        ]);
    }

    /**
     * Retrieve the form field description given by field name.
     *
     * @param string $field
     *
     * @throws \RuntimeException
     *
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    private function retrieveFormFieldDescription(AdminInterface $admin, $field)
    {
        $admin->getFormFieldDescriptions();

        $fieldDescription = $admin->getFormFieldDescription($field);

        if (!$fieldDescription) {
            throw new \RuntimeException(sprintf('The field "%s" does not exist.', $field));
        }

        if (null === $fieldDescription->getTargetEntity()) {
            throw new \RuntimeException(sprintf('No associated entity with field "%s".', $field));
        }

        return $fieldDescription;
    }

    /**
     * Retrieve the filter field description given by field name.
     *
     * @param string $field
     *
     * @throws \RuntimeException
     *
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    private function retrieveFilterFieldDescription(AdminInterface $admin, $field)
    {
        $admin->getFilterFieldDescriptions();

        $fieldDescription = $admin->getFilterFieldDescription($field);

        if (!$fieldDescription) {
            throw new \RuntimeException(sprintf('The field "%s" does not exist.', $field));
        }

        if (null === $fieldDescription->getTargetEntity()) {
            throw new \RuntimeException(sprintf('No associated entity with field "%s".', $field));
        }

        return $fieldDescription;
    }

    /**
     * @return FormRenderer|TwigRenderer
     */
    private function getFormRenderer()
    {
        // BC for Symfony < 3.2 where this runtime does not exists
        if (!method_exists(AppVariable::class, 'getToken')) {
            $extension = $this->twig->getExtension(FormExtension::class);
            $extension->initRuntime($this->twig);

            return $extension->renderer;
        }

        // BC for Symfony < 3.4 where runtime should be TwigRenderer
        if (!method_exists(DebugCommand::class, 'getLoaderPaths')) {
            $runtime = $this->twig->getRuntime(TwigRenderer::class);
            $runtime->setEnvironment($this->twig);

            return $runtime;
        }

        return $this->twig->getRuntime(FormRenderer::class);
    }
}
