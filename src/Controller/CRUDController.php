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

namespace Sonata\AdminBundle\Controller;

use Doctrine\Inflector\InflectorFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\AdminBundle\Util\AdminObjectAclData;
use Sonata\AdminBundle\Util\AdminObjectAclManipulator;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CRUDController implements ContainerAwareInterface
{
    // NEXT_MAJOR: Don't use these traits anymore (inherit from Controller instead)
    use ControllerTrait, ContainerAwareTrait {
        ControllerTrait::render as originalRender;
    }

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * The related Admin class.
     *
     * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0. Use `getCurrentAdmin()` from `sonata.admin.pool` service instead.
     *
     * @var AdminInterface
     */
    protected $admin;

    /**
     * The template registry of the related Admin class.
     *
     * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0. Use `$admin->getTemplateRegistry()` instead.
     *
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;

    public function setContainer(?ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->configure();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @see renderWithExtraParams()
     *
     * @param string               $view       The view name
     * @param array<string, mixed> $parameters An array of parameters to pass to the view
     *
     * @return Response A Response instance
     *
     * @deprecated since sonata-project/admin-bundle 3.27, to be removed in 4.0. Use Sonata\AdminBundle\Controller\CRUDController::renderWithExtraParams() instead.
     */
    public function render($view, array $parameters = [], ?Response $response = null)
    {
        @trigger_error(sprintf(
            'Method %1$s::render has been renamed to %1$s::renderWithExtraParams.',
            __CLASS__
        ), E_USER_DEPRECATED);

        return $this->renderWithExtraParams($view, $parameters, $response);
    }

    /**
     * Renders a view while passing mandatory parameters on to the template.
     *
     * @param string               $view       The view name
     * @param array<string, mixed> $parameters An array of parameters to pass to the view
     *
     * @return Response A Response instance
     */
    public function renderWithExtraParams($view, array $parameters = [], ?Response $response = null)
    {
        //NEXT_MAJOR: Remove method alias and use $this->render() directly.
        return $this->originalRender($view, $this->addRenderExtraParams($parameters), $response);
    }

    /**
     * List action.
     *
     * @throws AccessDeniedException If access is not granted
     *
     * @return Response
     */
    public function listAction()
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();
        $request = $this->getRequest();

        $admin->checkAccess('list');

        $preResponse = $this->preList($request);
        if (null !== $preResponse) {
            return $preResponse;
        }

        if ($listMode = $request->get('_list_mode')) {
            $admin->setListMode($listMode);
        }

        $datagrid = $admin->getDatagrid();
        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $admin->getFilterTheme());

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        $template = $admin->getTemplate('list');
        // $template = $admin->getTemplateRegistry()->getTemplate('list');

        return $this->renderWithExtraParams($template, [
            'action' => 'list',
            'form' => $formView,
            'datagrid' => $datagrid,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
            'export_formats' => $this->has('sonata.admin.admin_exporter') ?
                $this->get('sonata.admin.admin_exporter')->getAvailableFormats($admin) :
                $admin->getExportFormats(),
        ], null);
    }

    /**
     * Execute a batch delete.
     *
     * @throws AccessDeniedException If access is not granted
     *
     * @return RedirectResponse
     */
    public function batchActionDelete(ProxyQueryInterface $query)
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();

        $admin->checkAccess('batchDelete');

        $modelManager = $admin->getModelManager();

        try {
            $modelManager->batchDelete($admin->getClass(), $query);
            $this->addFlash(
                'sonata_flash_success',
                $this->trans('flash_batch_delete_success', [], 'SonataAdminBundle')
            );
        } catch (ModelManagerException $e) {
            $this->handleModelManagerException($e);
            $this->addFlash(
                'sonata_flash_error',
                $this->trans('flash_batch_delete_error', [], 'SonataAdminBundle')
            );
        }

        return $this->redirectToList();
    }

    /**
     * Delete action.
     *
     * @param int|string|null $id
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     *
     * @return Response|RedirectResponse
     */
    public function deleteAction($id) // NEXT_MAJOR: Remove the unused $id parameter
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();
        $request = $this->getRequest();
        $id = $request->get($admin->getIdParameter());
        $object = $admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }

        $this->checkParentChildAssociation($request, $object);

        $admin->checkAccess('delete', $object);

        $preResponse = $this->preDelete($request, $object);
        if (null !== $preResponse) {
            return $preResponse;
        }

        if (Request::METHOD_DELETE === $this->getRestMethod()) {
            // check the csrf token
            $this->validateCsrfToken('sonata.delete');

            $objectName = $admin->toString($object);

            try {
                $admin->delete($object);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(['result' => 'ok'], Response::HTTP_OK, []);
                }

                $this->addFlash(
                    'sonata_flash_success',
                    $this->trans(
                        'flash_delete_success',
                        ['%name%' => $this->escapeHtml($objectName)],
                        'SonataAdminBundle'
                    )
                );
            } catch (ModelManagerException $e) {
                $this->handleModelManagerException($e);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(['result' => 'error'], Response::HTTP_OK, []);
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $this->trans(
                        'flash_delete_error',
                        ['%name%' => $this->escapeHtml($objectName)],
                        'SonataAdminBundle'
                    )
                );
            }

            return $this->redirectTo($object);
        }

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        $template = $admin->getTemplate('delete');
        // $template = $admin->getTemplateRegistry()->getTemplate('delete');

        return $this->renderWithExtraParams($template, [
            'object' => $object,
            'action' => 'delete',
            'csrf_token' => $this->getCsrfToken('sonata.delete'),
        ], null);
    }

    /**
     * Edit action.
     *
     * @param int|string|null $deprecatedId
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     *
     * @return Response|RedirectResponse
     */
    public function editAction($deprecatedId = null) // NEXT_MAJOR: Remove the unused $id parameter
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();

        if (isset(\func_get_args()[0])) {
            @trigger_error(sprintf(
                'Support for the "id" route param as argument 1 at `%s()` is deprecated since'
                .' sonata-project/admin-bundle 3.62 and will be removed in 4.0,'
                .' use `AdminInterface::getIdParameter()` instead.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        // the key used to lookup the template
        $templateKey = 'edit';

        $request = $this->getRequest();
        $id = $request->get($admin->getIdParameter());
        $existingObject = $admin->getObject($id);

        if (!$existingObject) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }

        $this->checkParentChildAssociation($request, $existingObject);

        $admin->checkAccess('edit', $existingObject);

        $preResponse = $this->preEdit($request, $existingObject);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $admin->setSubject($existingObject);
        $objectId = $admin->getNormalizedIdentifier($existingObject);

        $form = $admin->getForm();

        $form->setData($existingObject);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                $submittedObject = $form->getData();
                $admin->setSubject($submittedObject);

                try {
                    $existingObject = $admin->update($submittedObject);

                    if ($this->isXmlHttpRequest()) {
                        return $this->handleXmlHttpRequestSuccessResponse($request, $existingObject);
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->trans(
                            'flash_edit_success',
                            ['%name%' => $this->escapeHtml($admin->toString($existingObject))],
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($existingObject);
                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                } catch (LockException $e) {
                    $this->addFlash('sonata_flash_error', $this->trans('flash_lock_error', [
                        '%name%' => $this->escapeHtml($admin->toString($existingObject)),
                        '%link_start%' => sprintf('<a href="%s">', $admin->generateObjectUrl('edit', $existingObject)),
                        '%link_end%' => '</a>',
                    ], 'SonataAdminBundle'));
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if ($this->isXmlHttpRequest() && null !== ($response = $this->handleXmlHttpRequestErrorResponse($request, $form))) {
                    return $response;
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $this->trans(
                        'flash_edit_error',
                        ['%name%' => $this->escapeHtml($admin->toString($existingObject))],
                        'SonataAdminBundle'
                    )
                );
            } elseif ($this->isPreviewRequested()) {
                // enable the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $admin->getShow();
            }
        }

        $formView = $form->createView();
        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $admin->getFormTheme());

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        $template = $admin->getTemplate($templateKey);
        // $template = $admin->getTemplateRegistry()->getTemplate($templateKey);

        return $this->renderWithExtraParams($template, [
            'action' => 'edit',
            'form' => $formView,
            'object' => $existingObject,
            'objectId' => $objectId,
        ], null);
    }

    /**
     * Batch action.
     *
     * @throws NotFoundHttpException If the HTTP method is not POST
     * @throws \RuntimeException     If the batch action is not defined
     *
     * @return Response|RedirectResponse
     */
    public function batchAction()
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();
        $request = $this->getRequest();
        $restMethod = $this->getRestMethod();

        if (Request::METHOD_POST !== $restMethod) {
            throw $this->createNotFoundException(sprintf(
                'Invalid request method given "%s", %s expected',
                $restMethod,
                Request::METHOD_POST
            ));
        }

        // check the csrf token
        $this->validateCsrfToken('sonata.batch');

        $confirmation = $request->get('confirmation', false);

        $forwardedRequest = $request->duplicate();

        if ($data = json_decode((string) $request->get('data'), true)) {
            $action = $data['action'];
            $idx = $data['idx'];
            $allElements = (bool) $data['all_elements'];
            $forwardedRequest->request->replace(array_merge($forwardedRequest->request->all(), $data));
        } else {
            $action = $forwardedRequest->request->get('action');
            /** @var InputBag|ParameterBag $bag */
            $bag = $request->request;
            if ($bag instanceof InputBag) {
                // symfony 5.1+
                $idx = $bag->all('idx');
            } else {
                $idx = $bag->get('idx', []);
            }
            $allElements = $forwardedRequest->request->getBoolean('all_elements');

            $forwardedRequest->request->set('idx', $idx);
            $forwardedRequest->request->set('all_elements', $allElements);

            $data = $forwardedRequest->request->all();

            unset($data['_sonata_csrf_token']);
        }

        // NEXT_MAJOR: Remove reflection check.
        $reflector = new \ReflectionMethod($admin, 'getBatchActions');
        if ($reflector->getDeclaringClass()->getName() === \get_class($admin)) {
            @trigger_error(sprintf(
                'Override %1$s::getBatchActions method is deprecated since version 3.2.'
                .' Use %1$s::configureBatchActions instead. The method will be final in 4.0.',
                AbstractAdmin::class
            ), E_USER_DEPRECATED);
        }
        $batchActions = $admin->getBatchActions();
        if (!\array_key_exists($action, $batchActions)) {
            throw new \RuntimeException(sprintf('The `%s` batch action is not defined', $action));
        }

        $camelizedAction = InflectorFactory::create()->build()->classify($action);
        $isRelevantAction = sprintf('batchAction%sIsRelevant', $camelizedAction);

        if (method_exists($this, $isRelevantAction)) {
            $nonRelevantMessage = $this->{$isRelevantAction}($idx, $allElements, $forwardedRequest);
        } else {
            $nonRelevantMessage = 0 !== \count($idx) || $allElements; // at least one item is selected
        }

        if (!$nonRelevantMessage) { // default non relevant message (if false of null)
            $nonRelevantMessage = 'flash_batch_empty';
        }

        $datagrid = $admin->getDatagrid();
        $datagrid->buildPager();

        if (true !== $nonRelevantMessage) {
            $this->addFlash(
                'sonata_flash_info',
                $this->trans($nonRelevantMessage, [], 'SonataAdminBundle')
            );

            return $this->redirectToList();
        }

        $askConfirmation = $batchActions[$action]['ask_confirmation'] ??
            true;

        if ($askConfirmation && 'ok' !== $confirmation) {
            $actionLabel = $batchActions[$action]['label'];
            $batchTranslationDomain = $batchActions[$action]['translation_domain'] ??
                $admin->getTranslationDomain();

            $formView = $datagrid->getForm()->createView();
            $this->setFormTheme($formView, $admin->getFilterTheme());

            // NEXT_MAJOR: Remove these lines and use commented lines below them instead
            $template = !empty($batchActions[$action]['template']) ?
                $batchActions[$action]['template'] :
                $admin->getTemplate('batch_confirmation');
            // $template = !empty($batchActions[$action]['template']) ?
            //     $batchActions[$action]['template'] :
            //     $admin->getTemplateRegistry()->getTemplate('batch_confirmation');

            return $this->renderWithExtraParams($template, [
                'action' => 'list',
                'action_label' => $actionLabel,
                'batch_translation_domain' => $batchTranslationDomain,
                'datagrid' => $datagrid,
                'form' => $formView,
                'data' => $data,
                'csrf_token' => $this->getCsrfToken('sonata.batch'),
            ], null);
        }

        // execute the action, batchActionXxxxx
        $finalAction = sprintf('batchAction%s', $camelizedAction);
        if (!method_exists($this, $finalAction)) {
            throw new \RuntimeException(sprintf('A `%s::%s` method must be callable', static::class, $finalAction));
        }

        $query = $datagrid->getQuery();

        $query->setFirstResult(null);
        $query->setMaxResults(null);

        $admin->preBatchAction($action, $query, $idx, $allElements);

        if (\count($idx) > 0) {
            $admin->getModelManager()->addIdentifiersToQuery($admin->getClass(), $query, $idx);
        } elseif (!$allElements) {
            $this->addFlash(
                'sonata_flash_info',
                $this->trans('flash_batch_no_elements_processed', [], 'SonataAdminBundle')
            );

            return $this->redirectToList();
        }

        return $this->{$finalAction}($query, $forwardedRequest);
    }

    /**
     * Create action.
     *
     * @throws AccessDeniedException If access is not granted
     *
     * @return Response
     */
    public function createAction()
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();
        $request = $this->getRequest();
        // the key used to lookup the template
        $templateKey = 'edit';

        $admin->checkAccess('create');

        $class = new \ReflectionClass($admin->hasActiveSubClass() ? $admin->getActiveSubClass() : $admin->getClass());

        if ($class->isAbstract()) {
            return $this->renderWithExtraParams(
                '@SonataAdmin/CRUD/select_subclass.html.twig',
                [
                    'base_template' => $this->getBaseTemplate(),
                    'admin' => $admin,
                    'action' => 'create',
                ],
                null
            );
        }

        $newObject = $admin->getNewInstance();

        $preResponse = $this->preCreate($request, $newObject);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $admin->setSubject($newObject);

        $form = $admin->getForm();

        $form->setData($newObject);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                $submittedObject = $form->getData();
                $admin->setSubject($submittedObject);
                $admin->checkAccess('create', $submittedObject);

                try {
                    $newObject = $admin->create($submittedObject);

                    if ($this->isXmlHttpRequest()) {
                        return $this->handleXmlHttpRequestSuccessResponse($request, $newObject);
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->trans(
                            'flash_create_success',
                            ['%name%' => $this->escapeHtml($admin->toString($newObject))],
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($newObject);
                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if ($this->isXmlHttpRequest() && null !== ($response = $this->handleXmlHttpRequestErrorResponse($request, $form))) {
                    return $response;
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $this->trans(
                        'flash_create_error',
                        ['%name%' => $this->escapeHtml($admin->toString($newObject))],
                        'SonataAdminBundle'
                    )
                );
            } elseif ($this->isPreviewRequested()) {
                // pick the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $admin->getShow();
            }
        }

        $formView = $form->createView();
        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $admin->getFormTheme());

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        $template = $admin->getTemplate($templateKey);
        // $template = $admin->getTemplateRegistry()->getTemplate($templateKey);

        return $this->renderWithExtraParams($template, [
            'action' => 'create',
            'form' => $formView,
            'object' => $newObject,
            'objectId' => null,
        ], null);
    }

    /**
     * Show action.
     *
     * @param int|string|null $deprecatedId
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     *
     * @return Response
     */
    public function showAction($deprecatedId = null) // NEXT_MAJOR: Remove the unused $id parameter
    {
        if (isset(\func_get_args()[0])) {
            @trigger_error(sprintf(
                'Support for the "id" route param as argument 1 at `%s()` is deprecated since'
                .' sonata-project/admin-bundle 3.62 and will be removed in 4.0,'
                .' use `AdminInterface::getIdParameter()` instead.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();
        $request = $this->getRequest();
        $id = $request->get($admin->getIdParameter());
        $object = $admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }

        $this->checkParentChildAssociation($request, $object);

        $admin->checkAccess('show', $object);

        $preResponse = $this->preShow($request, $object);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $admin->setSubject($object);

        $fields = $admin->getShow();
        \assert($fields instanceof FieldDescriptionCollection);

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        $template = $admin->getTemplate('show');
        //$template = $admin->getTemplateRegistry()->getTemplate('show');

        return $this->renderWithExtraParams($template, [
            'action' => 'show',
            'object' => $object,
            'elements' => $fields,
        ], null);
    }

    /**
     * Show history revisions for object.
     *
     * @param int|string|null $deprecatedId
     *
     * @throws AccessDeniedException If access is not granted
     * @throws NotFoundHttpException If the object does not exist or the audit reader is not available
     *
     * @return Response
     */
    public function historyAction($deprecatedId = null) // NEXT_MAJOR: Remove the unused $id parameter
    {
        if (isset(\func_get_args()[0])) {
            @trigger_error(sprintf(
                'Support for the "id" route param as argument 1 at `%s()` is deprecated since'
                .' sonata-project/admin-bundle 3.62 and will be removed in 4.0,'
                .' use `AdminInterface::getIdParameter()` instead.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();
        $request = $this->getRequest();
        $id = $request->get($admin->getIdParameter());
        $object = $admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }

        $admin->checkAccess('history', $object);

        $manager = $this->get('sonata.admin.audit.manager');

        if (!$manager->hasReader($admin->getClass())) {
            throw $this->createNotFoundException(sprintf(
                'unable to find the audit reader for class : %s',
                $admin->getClass()
            ));
        }

        $reader = $manager->getReader($admin->getClass());

        $revisions = $reader->findRevisions($admin->getClass(), $id);

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        $template = $admin->getTemplate('history');
        // $template = $admin->getTemplateRegistry()->getTemplate('history');

        return $this->renderWithExtraParams($template, [
            'action' => 'history',
            'object' => $object,
            'revisions' => $revisions,
            'currentRevision' => $revisions ? current($revisions) : false,
        ], null);
    }

    /**
     * View history revision of object.
     *
     * @param int|string|null $id
     * @param string|null     $revision
     *
     * @throws AccessDeniedException If access is not granted
     * @throws NotFoundHttpException If the object or revision does not exist or the audit reader is not available
     *
     * @return Response
     */
    public function historyViewRevisionAction($id = null, $revision = null) // NEXT_MAJOR: Remove the unused $id parameter
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();
        $request = $this->getRequest();
        $id = $request->get($admin->getIdParameter());
        $object = $admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }

        $admin->checkAccess('historyViewRevision', $object);

        $manager = $this->get('sonata.admin.audit.manager');

        if (!$manager->hasReader($admin->getClass())) {
            throw $this->createNotFoundException(sprintf(
                'unable to find the audit reader for class : %s',
                $admin->getClass()
            ));
        }

        $reader = $manager->getReader($admin->getClass());

        // retrieve the revisioned object
        $object = $reader->find($admin->getClass(), $id, $revision);

        if (!$object) {
            throw $this->createNotFoundException(sprintf(
                'unable to find the targeted object `%s` from the revision `%s` with classname : `%s`',
                $id,
                $revision,
                $admin->getClass()
            ));
        }

        $admin->setSubject($object);

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        $template = $admin->getTemplate('show');
        // $template = $admin->getTemplateRegistry()->getTemplate('show');

        return $this->renderWithExtraParams($template, [
            'action' => 'show',
            'object' => $object,
            'elements' => $admin->getShow(),
        ], null);
    }

    /**
     * Compare history revisions of object.
     *
     * @param int|string|null $id
     * @param int|string|null $base_revision
     * @param int|string|null $compare_revision
     *
     * @throws AccessDeniedException If access is not granted
     * @throws NotFoundHttpException If the object or revision does not exist or the audit reader is not available
     *
     * @return Response
     */
    public function historyCompareRevisionsAction($id = null, $base_revision = null, $compare_revision = null) // NEXT_MAJOR: Remove the unused $id parameter
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();

        $admin->checkAccess('historyCompareRevisions');

        $request = $this->getRequest();
        $id = $request->get($admin->getIdParameter());
        $object = $admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }

        $manager = $this->get('sonata.admin.audit.manager');

        if (!$manager->hasReader($admin->getClass())) {
            throw $this->createNotFoundException(sprintf(
                'unable to find the audit reader for class : %s',
                $admin->getClass()
            ));
        }

        $reader = $manager->getReader($admin->getClass());

        // retrieve the base revision
        $base_object = $reader->find($admin->getClass(), $id, $base_revision);
        if (!$base_object) {
            throw $this->createNotFoundException(sprintf(
                'unable to find the targeted object `%s` from the revision `%s` with classname : `%s`',
                $id,
                $base_revision,
                $admin->getClass()
            ));
        }

        // retrieve the compare revision
        $compare_object = $reader->find($admin->getClass(), $id, $compare_revision);
        if (!$compare_object) {
            throw $this->createNotFoundException(sprintf(
                'unable to find the targeted object `%s` from the revision `%s` with classname : `%s`',
                $id,
                $compare_revision,
                $admin->getClass()
            ));
        }

        $admin->setSubject($base_object);

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        $template = $admin->getTemplate('show_compare');
        // $template = $admin->getTemplateRegistry()->getTemplate('show_compare');

        return $this->renderWithExtraParams($template, [
            'action' => 'show',
            'object' => $base_object,
            'object_compare' => $compare_object,
            'elements' => $admin->getShow(),
        ], null);
    }

    /**
     * Export data to specified format.
     *
     * @throws AccessDeniedException If access is not granted
     * @throws \RuntimeException     If the export format is invalid
     *
     * @return Response
     */
    public function exportAction(Request $request)
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();

        $admin->checkAccess('export');

        $format = $request->get('format');

        // NEXT_MAJOR: remove the check
        if (!$this->has('sonata.admin.admin_exporter')) {
            @trigger_error(
                'Not registering the exporter bundle is deprecated since version 3.14. You must register it to be able to use the export action in 4.0.',
                E_USER_DEPRECATED
            );
            $allowedExportFormats = (array) $admin->getExportFormats();

            $class = (string) $admin->getClass();
            $filename = sprintf(
                'export_%s_%s.%s',
                strtolower((string) substr($class, strripos($class, '\\') + 1)),
                date('Y_m_d_H_i_s', strtotime('now')),
                $format
            );
            $exporter = $this->get('sonata.admin.exporter');
        } else {
            $adminExporter = $this->get('sonata.admin.admin_exporter');
            $allowedExportFormats = $adminExporter->getAvailableFormats($admin);
            $filename = $adminExporter->getExportFilename($admin, $format);
            $exporter = $this->get('sonata.exporter.exporter');
        }

        if (!\in_array($format, $allowedExportFormats, true)) {
            throw new \RuntimeException(sprintf(
                'Export in format `%s` is not allowed for class: `%s`. Allowed formats are: `%s`',
                $format,
                $admin->getClass(),
                implode(', ', $allowedExportFormats)
            ));
        }

        return $exporter->getResponse(
            $format,
            $filename,
            $admin->getDataSourceIterator()
        );
    }

    /**
     * Returns the Response object associated to the acl action.
     *
     * @param int|string|null $deprecatedId
     *
     * @throws AccessDeniedException If access is not granted
     * @throws NotFoundHttpException If the object does not exist or the ACL is not enabled
     *
     * @return Response|RedirectResponse
     */
    public function aclAction($deprecatedId = null) // NEXT_MAJOR: Remove the unused $id parameter
    {
        if (isset(\func_get_args()[0])) {
            @trigger_error(sprintf(
                'Support for the "id" route param as argument 1 at `%s()` is deprecated since'
                .' sonata-project/admin-bundle 3.62 and will be removed in 4.0,'
                .' use `AdminInterface::getIdParameter()` instead.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();

        if (!$admin->isAclEnabled()) {
            throw $this->createNotFoundException('ACL are not enabled for this admin');
        }

        $request = $this->getRequest();
        $id = $request->get($admin->getIdParameter());
        $object = $admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }

        $admin->checkAccess('acl', $object);

        $admin->setSubject($object);
        $aclUsers = $this->getAclUsers();
        $aclRoles = $this->getAclRoles();

        $adminObjectAclManipulator = $this->get('sonata.admin.object.manipulator.acl.admin');
        $adminObjectAclData = new AdminObjectAclData(
            $admin,
            $object,
            $aclUsers,
            $adminObjectAclManipulator->getMaskBuilderClass(),
            $aclRoles
        );

        $aclUsersForm = $adminObjectAclManipulator->createAclUsersForm($adminObjectAclData);
        $aclRolesForm = $adminObjectAclManipulator->createAclRolesForm($adminObjectAclData);

        if (Request::METHOD_POST === $request->getMethod()) {
            if ($request->request->has(AdminObjectAclManipulator::ACL_USERS_FORM_NAME)) {
                $form = $aclUsersForm;
                $updateMethod = 'updateAclUsers';
            } elseif ($request->request->has(AdminObjectAclManipulator::ACL_ROLES_FORM_NAME)) {
                $form = $aclRolesForm;
                $updateMethod = 'updateAclRoles';
            }

            if (isset($form, $updateMethod)) {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $adminObjectAclManipulator->$updateMethod($adminObjectAclData);
                    $this->addFlash(
                        'sonata_flash_success',
                        $this->trans('flash_acl_edit_success', [], 'SonataAdminBundle')
                    );

                    return new RedirectResponse($admin->generateObjectUrl('acl', $object));
                }
            }
        }

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        $template = $admin->getTemplate('acl');
        // $template = $admin->getTemplateRegistry()->getTemplate('acl');

        return $this->renderWithExtraParams($template, [
            'action' => 'acl',
            'permissions' => $adminObjectAclData->getUserPermissions(),
            'object' => $object,
            'users' => $aclUsers,
            'roles' => $aclRoles,
            'aclUsersForm' => $aclUsersForm->createView(),
            'aclRolesForm' => $aclRolesForm->createView(),
        ], null);
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    protected function addRenderExtraParams(array $parameters = []): array
    {
        if (!$this->isXmlHttpRequest()) {
            $parameters['breadcrumbs_builder'] = $this->get('sonata.admin.breadcrumbs_builder');
        }

        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();
        $parameters['admin'] = $parameters['admin'] ?? $admin;
        $parameters['base_template'] = $parameters['base_template'] ?? $this->getBaseTemplate();
        $parameters['admin_pool'] = $this->get('sonata.admin.pool');

        return $parameters;
    }

    /**
     * Gets a container configuration parameter by its name.
     *
     * @param string $name The parameter name
     *
     * @return mixed
     */
    protected function getParameter($name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * Render JSON.
     *
     * @param mixed $data
     * @param int   $status
     * @param array $headers
     *
     * @return JsonResponse with json encoded data
     */
    protected function renderJson($data, $status = Response::HTTP_OK, $headers = [])
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * Returns true if the request is a XMLHttpRequest.
     *
     * @return bool True if the request is an XMLHttpRequest, false otherwise
     */
    protected function isXmlHttpRequest()
    {
        $request = $this->getRequest();

        return $request->isXmlHttpRequest() || $request->get('_xml_http_request');
    }

    /**
     * Returns the correct RESTful verb, given either by the request itself or
     * via the "_method" parameter.
     *
     * @return string HTTP method, either
     */
    protected function getRestMethod()
    {
        $request = $this->getRequest();

        if (Request::getHttpMethodParameterOverride() || !$request->request->has('_method')) {
            return $request->getMethod();
        }

        return $request->request->get('_method');
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * Contextualize the admin class depends on the current request.
     *
     * @throws \RuntimeException
     */
    protected function configure()
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();

        // NEXT_MAJOR: remove this if. It is unnesessery becouse template registry is get directly from admin.
        $this->templateRegistry = $this->container->get(sprintf('%s.template_registry', $admin->getCode()));
        if (!$this->templateRegistry instanceof TemplateRegistryInterface) {
            throw new \RuntimeException(sprintf(
                'Unable to find the template registry related to the current admin (%s)',
                $admin->getCode()
            ));
        }

        $this->admin = $admin;
    }

    /**
     * Proxy for the logger service of the container.
     * If no such service is found, a NullLogger is returned.
     *
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        if ($this->container->has('logger')) {
            $logger = $this->container->get('logger');
            \assert($logger instanceof LoggerInterface);

            return $logger;
        }

        return new NullLogger();
    }

    /**
     * Returns the base template name.
     *
     * @return string The template name
     */
    protected function getBaseTemplate()
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();

        if ($this->isXmlHttpRequest()) {
            // NEXT_MAJOR: Remove this line and use commented line below it instead
            return $admin->getTemplate('ajax');
            // return $admin->getTemplateRegistry()->getTemplate('ajax');
        }

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        return $admin->getTemplate('layout');
        // return $admin->getTemplateRegistry()->getTemplate('layout');
    }

    /**
     * @throws \Exception
     */
    protected function handleModelManagerException(\Exception $e)
    {
        if ($this->get('kernel')->isDebug()) {
            throw $e;
        }

        $context = ['exception' => $e];
        if ($e->getPrevious()) {
            $context['previous_exception_message'] = $e->getPrevious()->getMessage();
        }
        $this->getLogger()->error($e->getMessage(), $context);
    }

    /**
     * Redirect the user depend on this choice.
     *
     * @param object $object
     *
     * @return RedirectResponse
     */
    protected function redirectTo($object)
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();
        $request = $this->getRequest();

        $url = false;

        if (null !== $request->get('btn_update_and_list')) {
            return $this->redirectToList();
        }
        if (null !== $request->get('btn_create_and_list')) {
            return $this->redirectToList();
        }

        if (null !== $request->get('btn_create_and_create')) {
            $params = [];
            if ($admin->hasActiveSubClass()) {
                $params['subclass'] = $request->get('subclass');
            }
            $url = $admin->generateUrl('create', $params);
        }

        if ('DELETE' === $this->getRestMethod()) {
            return $this->redirectToList();
        }

        if (!$url) {
            foreach (['edit', 'show'] as $route) {
                if ($admin->hasRoute($route) && $admin->hasAccess($route, $object)) {
                    $url = $admin->generateObjectUrl(
                        $route,
                        $object,
                        $this->getSelectedTab($request)
                    );

                    break;
                }
            }
        }

        if (!$url) {
            return $this->redirectToList();
        }

        return new RedirectResponse($url);
    }

    /**
     * Redirects the user to the list view.
     *
     * @return RedirectResponse
     */
    final protected function redirectToList()
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();
        $parameters = [];

        if ($filter = $admin->getFilterParameters()) {
            $parameters['filter'] = $filter;
        }

        return $this->redirect($admin->generateUrl('list', $parameters));
    }

    /**
     * Returns true if the preview is requested to be shown.
     *
     * @return bool
     */
    protected function isPreviewRequested()
    {
        $request = $this->getRequest();

        return null !== $request->get('btn_preview');
    }

    /**
     * Returns true if the preview has been approved.
     *
     * @return bool
     */
    protected function isPreviewApproved()
    {
        $request = $this->getRequest();

        return null !== $request->get('btn_preview_approve');
    }

    /**
     * Returns true if the request is in the preview workflow.
     *
     * That means either a preview is requested or the preview has already been shown
     * and it got approved/declined.
     *
     * @return bool
     */
    protected function isInPreviewMode()
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();

        return $admin->supportsPreviewMode()
        && ($this->isPreviewRequested()
            || $this->isPreviewApproved()
            || $this->isPreviewDeclined());
    }

    /**
     * Returns true if the preview has been declined.
     *
     * @return bool
     */
    protected function isPreviewDeclined()
    {
        $request = $this->getRequest();

        return null !== $request->get('btn_preview_decline');
    }

    /**
     * Gets ACL users.
     *
     * @return \Traversable
     */
    protected function getAclUsers()
    {
        $aclUsers = [];

        $userManagerServiceName = $this->container->getParameter('sonata.admin.security.acl_user_manager');
        if (null !== $userManagerServiceName && $this->has($userManagerServiceName)) {
            $userManager = $this->get($userManagerServiceName);

            if (method_exists($userManager, 'findUsers')) {
                $aclUsers = $userManager->findUsers();
            }
        }

        return \is_array($aclUsers) ? new \ArrayIterator($aclUsers) : $aclUsers;
    }

    /**
     * Gets ACL roles.
     *
     * @return \Traversable
     */
    protected function getAclRoles()
    {
        $aclRoles = [];
        $roleHierarchy = $this->container->getParameter('security.role_hierarchy.roles');
        $pool = $this->container->get('sonata.admin.pool');

        foreach ($pool->getAdminServiceIds() as $id) {
            try {
                $admin = $pool->getInstance($id);
            } catch (\Exception $e) {
                continue;
            }

            $baseRole = $admin->getSecurityHandler()->getBaseRole($admin);
            foreach ($admin->getSecurityInformation() as $role => $permissions) {
                $role = sprintf($baseRole, $role);
                $aclRoles[] = $role;
            }
        }

        foreach ($roleHierarchy as $name => $roles) {
            $aclRoles[] = $name;
            $aclRoles = array_merge($aclRoles, $roles);
        }

        $aclRoles = array_unique($aclRoles);

        return new \ArrayIterator($aclRoles);
    }

    /**
     * Validate CSRF token for action without form.
     *
     * @param string $intention
     *
     * @throws HttpException
     */
    protected function validateCsrfToken($intention)
    {
        $request = $this->getRequest();
        $token = $request->get('_sonata_csrf_token');

        if ($this->container->has('security.csrf.token_manager')) {
            $valid = $this->container->get('security.csrf.token_manager')->isTokenValid(new CsrfToken($intention, $token));
        } else {
            return;
        }

        if (!$valid) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'The csrf token is not valid, CSRF attack?');
        }
    }

    /**
     * Escape string for html output.
     *
     * @param string $s
     *
     * @return string
     */
    protected function escapeHtml($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Get CSRF token.
     *
     * @param string $intention
     *
     * @return string|false
     */
    protected function getCsrfToken($intention)
    {
        if ($this->container->has('security.csrf.token_manager')) {
            return $this->container->get('security.csrf.token_manager')->getToken($intention)->getValue();
        }

        return false;
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from createAction.
     *
     * @param object $object
     *
     * @return Response|null
     */
    protected function preCreate(Request $request, $object)
    {
        return null;
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from editAction.
     *
     * @param object $object
     *
     * @return Response|null
     */
    protected function preEdit(Request $request, $object)
    {
        return null;
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from deleteAction.
     *
     * @param object $object
     *
     * @return Response|null
     */
    protected function preDelete(Request $request, $object)
    {
        return null;
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from showAction.
     *
     * @param object $object
     *
     * @return Response|null
     */
    protected function preShow(Request $request, $object)
    {
        return null;
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from listAction.
     *
     * @return Response|null
     */
    protected function preList(Request $request)
    {
        return null;
    }

    /**
     * Translate a message id.
     *
     * @param string $id
     * @param string $domain
     * @param string $locale
     *
     * @return string translated string
     */
    final protected function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();
        $domain = $domain ?: $admin->getTranslationDomain();

        return $this->get('translator')->trans($id, $parameters, $domain, $locale);
    }

    private function getSelectedTab(Request $request): array
    {
        return array_filter(['_tab' => $request->request->get('_tab')]);
    }

    private function checkParentChildAssociation(Request $request, object $object): void
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();

        if (!$admin->isChild()) {
            return;
        }

        // NEXT_MAJOR: remove this check
        if (!$admin->getParentAssociationMapping()) {
            return;
        }

        $parentAdmin = $admin->getParent();
        $parentId = $request->get($parentAdmin->getIdParameter());

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $propertyPath = new PropertyPath($admin->getParentAssociationMapping());

        if ($parentAdmin->getObject($parentId) !== $propertyAccessor->getValue($object, $propertyPath)) {
            // NEXT_MAJOR: make this exception
            @trigger_error(
                'Accessing a child that isn\'t connected to a given parent is deprecated since sonata-project/admin-bundle 3.34 and won\'t be allowed in 4.0.',
                E_USER_DEPRECATED
            );
        }
    }

    /**
     * Sets the admin form theme to form view. Used for compatibility between Symfony versions.
     */
    private function setFormTheme(FormView $formView, ?array $theme = null): void
    {
        $twig = $this->get('twig');

        $twig->getRuntime(FormRenderer::class)->setTheme($formView, $theme);
    }

    private function handleXmlHttpRequestErrorResponse(Request $request, FormInterface $form): ?JsonResponse
    {
        if (empty(array_intersect(['application/json', '*/*'], $request->getAcceptableContentTypes()))) {
            @trigger_error('In next major version response will return 406 NOT ACCEPTABLE without `Accept: application/json` or `Accept: */*`', E_USER_DEPRECATED);

            return null;
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $this->renderJson([
            'result' => 'error',
            'errors' => $errors,
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param object $object
     */
    private function handleXmlHttpRequestSuccessResponse(Request $request, $object): JsonResponse
    {
        $admin = $this->container->get('sonata.admin.pool')->getCurrentAdmin();

        if (empty(array_intersect(['application/json', '*/*'], $request->getAcceptableContentTypes()))) {
            @trigger_error('In next major version response will return 406 NOT ACCEPTABLE without `Accept: application/json` or `Accept: */*`', E_USER_DEPRECATED);
        }

        return $this->renderJson([
            'result' => 'ok',
            'objectId' => $admin->getNormalizedIdentifier($object),
            'objectName' => $this->escapeHtml($admin->toString($object)),
        ], Response::HTTP_OK);
    }
}
