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

use Doctrine\Common\Inflector\Inflector;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Util\AdminObjectAclData;
use Sonata\AdminBundle\Util\AdminObjectAclManipulator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * Class CRUDController.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CRUDController extends Controller
{
    /**
     * The related Admin class.
     *
     * @var AdminInterface
     */
    protected $admin;

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->configure();
    }

    /**
     * {@inheritdoc}
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        if (!$this->isXmlHttpRequest()) {
            $parameters['breadcrumbs_builder'] = $this->get('sonata.admin.breadcrumbs_builder');
        }
        $parameters['admin'] = isset($parameters['admin']) ?
            $parameters['admin'] :
            $this->admin;

        $parameters['base_template'] = isset($parameters['base_template']) ?
            $parameters['base_template'] :
            $this->getBaseTemplate();

        $parameters['admin_pool'] = $this->get('sonata.admin.pool');

        return parent::render($view, $parameters, $response);
    }

    /**
     * List action.
     *
     * @return Response
     *
     * @throws AccessDeniedException If access is not granted
     */
    public function listAction()
    {
        $request = $this->getRequest();

        $this->admin->checkAccess('list');

        $preResponse = $this->preList($request);
        if ($preResponse !== null) {
            return $preResponse;
        }

        if ($listMode = $request->get('_list_mode')) {
            $this->admin->setListMode($listMode);
        }

        $datagrid = $this->admin->getDatagrid();
        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        return $this->render($this->admin->getTemplate('list'), array(
            'action' => 'list',
            'form' => $formView,
            'datagrid' => $datagrid,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
        ), null, $request);
    }

    /**
     * Execute a batch delete.
     *
     * @param ProxyQueryInterface $query
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException If access is not granted
     */
    public function batchActionDelete(ProxyQueryInterface $query)
    {
        $this->admin->checkAccess('batchDelete');

        $modelManager = $this->admin->getModelManager();
        try {
            $modelManager->batchDelete($this->admin->getClass(), $query);
            $this->addFlash('sonata_flash_success', 'flash_batch_delete_success');
        } catch (ModelManagerException $e) {
            $this->handleModelManagerException($e);
            $this->addFlash('sonata_flash_error', 'flash_batch_delete_error');
        }

        return new RedirectResponse($this->admin->generateUrl(
            'list',
            array('filter' => $this->admin->getFilterParameters())
        ));
    }

    /**
     * Delete action.
     *
     * @param int|string|null $id
     *
     * @return Response|RedirectResponse
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     */
    public function deleteAction($id)
    {
        $request = $this->getRequest();
        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->admin->checkAccess('delete', $object);

        $preResponse = $this->preDelete($request, $object);
        if ($preResponse !== null) {
            return $preResponse;
        }

        if ($this->getRestMethod() == 'DELETE') {
            // check the csrf token
            $this->validateCsrfToken('sonata.delete');

            $objectName = $this->admin->toString($object);

            try {
                $this->admin->delete($object);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array('result' => 'ok'), 200, array());
                }

                $this->addFlash(
                    'sonata_flash_success',
                    $this->trans(
                        'flash_delete_success',
                        array('%name%' => $this->escapeHtml($objectName)),
                        'SonataAdminBundle'
                    )
                );
            } catch (ModelManagerException $e) {
                $this->handleModelManagerException($e);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array('result' => 'error'), 200, array());
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $this->trans(
                        'flash_delete_error',
                        array('%name%' => $this->escapeHtml($objectName)),
                        'SonataAdminBundle'
                    )
                );
            }

            return $this->redirectTo($object);
        }

        return $this->render($this->admin->getTemplate('delete'), array(
            'object' => $object,
            'action' => 'delete',
            'csrf_token' => $this->getCsrfToken('sonata.delete'),
        ), null);
    }

    /**
     * Edit action.
     *
     * @param int|string|null $id
     *
     * @return Response|RedirectResponse
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     */
    public function editAction($id = null)
    {
        $request = $this->getRequest();
        // the key used to lookup the template
        $templateKey = 'edit';

        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->admin->checkAccess('edit', $object);

        $preResponse = $this->preEdit($request, $object);
        if ($preResponse !== null) {
            return $preResponse;
        }

        $this->admin->setSubject($object);

        /** @var $form Form */
        $form = $this->admin->getForm();
        $form->setData($object);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //TODO: remove this check for 4.0
            if (method_exists($this->admin, 'preValidate')) {
                $this->admin->preValidate($object);
            }
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                try {
                    $object = $this->admin->update($object);

                    if ($this->isXmlHttpRequest()) {
                        return $this->renderJson(array(
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($object),
                            'objectName' => $this->escapeHtml($this->admin->toString($object)),
                        ), 200, array());
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->trans(
                            'flash_edit_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($object);
                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                } catch (LockException $e) {
                    $this->addFlash('sonata_flash_error', $this->trans('flash_lock_error', array(
                        '%name%' => $this->escapeHtml($this->admin->toString($object)),
                        '%link_start%' => '<a href="'.$this->admin->generateObjectUrl('edit', $object).'">',
                        '%link_end%' => '</a>',
                    ), 'SonataAdminBundle'));
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->trans(
                            'flash_edit_error',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );
                }
            } elseif ($this->isPreviewRequested()) {
                // enable the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'edit',
            'form' => $view,
            'object' => $object,
        ), null);
    }

    /**
     * Batch action.
     *
     * @param Request $request
     *
     * @return Response|RedirectResponse
     *
     * @throws NotFoundHttpException If the HTTP method is not POST
     * @throws \RuntimeException     If the batch action is not defined
     */
    public function batchAction()
    {
        $request = $this->getRequest();
        $restMethod = $this->getRestMethod();

        if ('POST' !== $restMethod) {
            throw $this->createNotFoundException(sprintf('Invalid request type "%s", POST expected', $restMethod));
        }

        // check the csrf token
        $this->validateCsrfToken('sonata.batch');

        $confirmation = $request->get('confirmation', false);

        if ($data = json_decode($request->get('data'), true)) {
            $action = $data['action'];
            $idx = $data['idx'];
            $allElements = $data['all_elements'];
            $request->request->replace(array_merge($request->request->all(), $data));
        } else {
            $request->request->set('idx', $request->get('idx', array()));
            $request->request->set('all_elements', $request->get('all_elements', false));

            $action = $request->get('action');
            $idx = $request->get('idx');
            $allElements = $request->get('all_elements');
            $data = $request->request->all();

            unset($data['_sonata_csrf_token']);
        }

        // NEXT_MAJOR: Remove reflection check.
        $reflector = new \ReflectionMethod($this->admin, 'getBatchActions');
        if ($reflector->getDeclaringClass()->getName() === get_class($this->admin)) {
            @trigger_error('Override Sonata\AdminBundle\Admin\AbstractAdmin::getBatchActions method'
                .' is deprecated since version 3.2.'
                .' Use Sonata\AdminBundle\Admin\AbstractAdmin::configureBatchActions instead.'
                .' The method will be final in 4.0.', E_USER_DEPRECATED
            );
        }
        $batchActions = $this->admin->getBatchActions();
        if (!array_key_exists($action, $batchActions)) {
            throw new \RuntimeException(sprintf('The `%s` batch action is not defined', $action));
        }

        $camelizedAction = Inflector::classify($action);
        $isRelevantAction = sprintf('batchAction%sIsRelevant', $camelizedAction);

        if (method_exists($this, $isRelevantAction)) {
            $nonRelevantMessage = call_user_func(array($this, $isRelevantAction), $idx, $allElements);
        } else {
            $nonRelevantMessage = count($idx) != 0 || $allElements; // at least one item is selected
        }

        if (!$nonRelevantMessage) { // default non relevant message (if false of null)
            $nonRelevantMessage = 'flash_batch_empty';
        }

        $datagrid = $this->admin->getDatagrid();
        $datagrid->buildPager();

        if (true !== $nonRelevantMessage) {
            $this->addFlash('sonata_flash_info', $nonRelevantMessage);

            return new RedirectResponse(
                $this->admin->generateUrl(
                    'list',
                    array('filter' => $this->admin->getFilterParameters())
                )
            );
        }

        $askConfirmation = isset($batchActions[$action]['ask_confirmation']) ?
            $batchActions[$action]['ask_confirmation'] :
            true;

        if ($askConfirmation && $confirmation != 'ok') {
            $actionLabel = $batchActions[$action]['label'];
            $batchTranslationDomain = isset($batchActions[$action]['translation_domain']) ?
                $batchActions[$action]['translation_domain'] :
                $this->admin->getTranslationDomain();

            $formView = $datagrid->getForm()->createView();

            return $this->render($this->admin->getTemplate('batch_confirmation'), array(
                'action' => 'list',
                'action_label' => $actionLabel,
                'batch_translation_domain' => $batchTranslationDomain,
                'datagrid' => $datagrid,
                'form' => $formView,
                'data' => $data,
                'csrf_token' => $this->getCsrfToken('sonata.batch'),
            ), null);
        }

        // execute the action, batchActionXxxxx
        $finalAction = sprintf('batchAction%s', $camelizedAction);
        if (!is_callable(array($this, $finalAction))) {
            throw new \RuntimeException(sprintf('A `%s::%s` method must be callable', get_class($this), $finalAction));
        }

        $query = $datagrid->getQuery();

        $query->setFirstResult(null);
        $query->setMaxResults(null);

        $this->admin->preBatchAction($action, $query, $idx, $allElements);

        if (count($idx) > 0) {
            $this->admin->getModelManager()->addIdentifiersToQuery($this->admin->getClass(), $query, $idx);
        } elseif (!$allElements) {
            $query = null;
        }

        return call_user_func(array($this, $finalAction), $query);
    }

    /**
     * Create action.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws AccessDeniedException If access is not granted
     */
    public function createAction()
    {
        $request = $this->getRequest();
        // the key used to lookup the template
        $templateKey = 'edit';

        $this->admin->checkAccess('create');

        $class = new \ReflectionClass($this->admin->hasActiveSubClass() ? $this->admin->getActiveSubClass() : $this->admin->getClass());

        if ($class->isAbstract()) {
            return $this->render(
                'SonataAdminBundle:CRUD:select_subclass.html.twig',
                array(
                    'base_template' => $this->getBaseTemplate(),
                    'admin' => $this->admin,
                    'action' => 'create',
                ),
                null,
                $request
            );
        }

        $object = $this->admin->getNewInstance();

        $preResponse = $this->preCreate($request, $object);
        if ($preResponse !== null) {
            return $preResponse;
        }

        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //TODO: remove this check for 4.0
            if (method_exists($this->admin, 'preValidate')) {
                $this->admin->preValidate($object);
            }
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode($request) || $this->isPreviewApproved($request))) {
                $this->admin->checkAccess('create', $object);

                try {
                    $object = $this->admin->create($object);

                    if ($this->isXmlHttpRequest()) {
                        return $this->renderJson(array(
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($object),
                        ), 200, array());
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->trans(
                            'flash_create_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($object);
                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->trans(
                            'flash_create_error',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );
                }
            } elseif ($this->isPreviewRequested()) {
                // pick the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'create',
            'form' => $view,
            'object' => $object,
        ), null);
    }

    /**
     * Show action.
     *
     * @param int|string|null $id
     * @param Request         $request
     *
     * @return Response
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     */
    public function showAction($id = null)
    {
        $request = $this->getRequest();
        $id = $request->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->admin->checkAccess('show', $object);

        $preResponse = $this->preShow($request, $object);
        if ($preResponse !== null) {
            return $preResponse;
        }

        $this->admin->setSubject($object);

        return $this->render($this->admin->getTemplate('show'), array(
            'action' => 'show',
            'object' => $object,
            'elements' => $this->admin->getShow(),
        ), null);
    }

    /**
     * Show history revisions for object.
     *
     * @param int|string|null $id
     * @param Request         $request
     *
     * @return Response
     *
     * @throws AccessDeniedException If access is not granted
     * @throws NotFoundHttpException If the object does not exist or the audit reader is not available
     */
    public function historyAction($id = null)
    {
        $request = $this->getRequest();
        $id = $request->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->admin->checkAccess('history', $object);

        $manager = $this->get('sonata.admin.audit.manager');

        if (!$manager->hasReader($this->admin->getClass())) {
            throw $this->createNotFoundException(
                sprintf(
                    'unable to find the audit reader for class : %s',
                    $this->admin->getClass()
                )
            );
        }

        $reader = $manager->getReader($this->admin->getClass());

        $revisions = $reader->findRevisions($this->admin->getClass(), $id);

        return $this->render($this->admin->getTemplate('history'), array(
            'action' => 'history',
            'object' => $object,
            'revisions' => $revisions,
            'currentRevision' => $revisions ? current($revisions) : false,
        ), null, $request);
    }

    /**
     * View history revision of object.
     *
     * @param int|string|null $id
     * @param string|null     $revision
     * @param Request         $request
     *
     * @return Response
     *
     * @throws AccessDeniedException If access is not granted
     * @throws NotFoundHttpException If the object or revision does not exist or the audit reader is not available
     */
    public function historyViewRevisionAction($id = null, $revision = null)
    {
        $request = $this->getRequest();
        $id = $request->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->admin->checkAccess('historyViewRevision', $object);

        $manager = $this->get('sonata.admin.audit.manager');

        if (!$manager->hasReader($this->admin->getClass())) {
            throw $this->createNotFoundException(
                sprintf(
                    'unable to find the audit reader for class : %s',
                    $this->admin->getClass()
                )
            );
        }

        $reader = $manager->getReader($this->admin->getClass());

        // retrieve the revisioned object
        $object = $reader->find($this->admin->getClass(), $id, $revision);

        if (!$object) {
            throw $this->createNotFoundException(
                sprintf(
                    'unable to find the targeted object `%s` from the revision `%s` with classname : `%s`',
                    $id,
                    $revision,
                    $this->admin->getClass()
                )
            );
        }

        $this->admin->setSubject($object);

        return $this->render($this->admin->getTemplate('show'), array(
            'action' => 'show',
            'object' => $object,
            'elements' => $this->admin->getShow(),
        ), null);
    }

    /**
     * Compare history revisions of object.
     *
     * @param int|string|null $id
     * @param int|string|null $base_revision
     * @param int|string|null $compare_revision
     * @param Request         $request
     *
     * @return Response
     *
     * @throws AccessDeniedException If access is not granted
     * @throws NotFoundHttpException If the object or revision does not exist or the audit reader is not available
     */
    public function historyCompareRevisionsAction($id = null, $base_revision = null, $compare_revision = null)
    {
        $request = $this->getRequest();

        $this->admin->checkAccess('historyCompareRevisions');

        $id = $request->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
        }

        $manager = $this->get('sonata.admin.audit.manager');

        if (!$manager->hasReader($this->admin->getClass())) {
            throw $this->createNotFoundException(
                sprintf(
                    'unable to find the audit reader for class : %s',
                    $this->admin->getClass()
                )
            );
        }

        $reader = $manager->getReader($this->admin->getClass());

        // retrieve the base revision
        $base_object = $reader->find($this->admin->getClass(), $id, $base_revision);
        if (!$base_object) {
            throw $this->createNotFoundException(
                sprintf(
                    'unable to find the targeted object `%s` from the revision `%s` with classname : `%s`',
                    $id,
                    $base_revision,
                    $this->admin->getClass()
                )
            );
        }

        // retrieve the compare revision
        $compare_object = $reader->find($this->admin->getClass(), $id, $compare_revision);
        if (!$compare_object) {
            throw $this->createNotFoundException(
                sprintf(
                    'unable to find the targeted object `%s` from the revision `%s` with classname : `%s`',
                    $id,
                    $compare_revision,
                    $this->admin->getClass()
                )
            );
        }

        $this->admin->setSubject($base_object);

        return $this->render($this->admin->getTemplate('show_compare'), array(
            'action' => 'show',
            'object' => $base_object,
            'object_compare' => $compare_object,
            'elements' => $this->admin->getShow(),
        ), null);
    }

    /**
     * Export data to specified format.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws AccessDeniedException If access is not granted
     * @throws \RuntimeException     If the export format is invalid
     */
    public function exportAction(Request $request)
    {
        $this->admin->checkAccess('export');

        $format = $request->get('format');

        $allowedExportFormats = (array) $this->admin->getExportFormats();

        if (!in_array($format, $allowedExportFormats)) {
            throw new \RuntimeException(
                sprintf(
                    'Export in format `%s` is not allowed for class: `%s`. Allowed formats are: `%s`',
                    $format,
                    $this->admin->getClass(),
                    implode(', ', $allowedExportFormats)
                )
            );
        }

        $filename = sprintf(
            'export_%s_%s.%s',
            strtolower(substr($this->admin->getClass(), strripos($this->admin->getClass(), '\\') + 1)),
            date('Y_m_d_H_i_s', strtotime('now')),
            $format
        );

        return $this->get('sonata.admin.exporter')->getResponse(
            $format,
            $filename,
            $this->admin->getDataSourceIterator()
        );
    }

    /**
     * Returns the Response object associated to the acl action.
     *
     * @param int|string|null $id
     * @param Request         $request
     *
     * @return Response|RedirectResponse
     *
     * @throws AccessDeniedException If access is not granted
     * @throws NotFoundHttpException If the object does not exist or the ACL is not enabled
     */
    public function aclAction($id = null)
    {
        $request = $this->getRequest();

        if (!$this->admin->isAclEnabled()) {
            throw $this->createNotFoundException('ACL are not enabled for this admin');
        }

        $id = $request->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->admin->checkAccess('acl', $object);

        $this->admin->setSubject($object);
        $aclUsers = $this->getAclUsers();
        $aclRoles = $this->getAclRoles();

        $adminObjectAclManipulator = $this->get('sonata.admin.object.manipulator.acl.admin');
        $adminObjectAclData = new AdminObjectAclData(
            $this->admin,
            $object,
            $aclUsers,
            $adminObjectAclManipulator->getMaskBuilderClass(),
            $aclRoles
        );

        $aclUsersForm = $adminObjectAclManipulator->createAclUsersForm($adminObjectAclData);
        $aclRolesForm = $adminObjectAclManipulator->createAclRolesForm($adminObjectAclData);

        if ($request->getMethod() === 'POST') {
            if ($request->request->has(AdminObjectAclManipulator::ACL_USERS_FORM_NAME)) {
                $form = $aclUsersForm;
                $updateMethod = 'updateAclUsers';
            } elseif ($request->request->has(AdminObjectAclManipulator::ACL_ROLES_FORM_NAME)) {
                $form = $aclRolesForm;
                $updateMethod = 'updateAclRoles';
            }

            if (isset($form)) {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $adminObjectAclManipulator->$updateMethod($adminObjectAclData);
                    $this->addFlash('sonata_flash_success', 'flash_acl_edit_success');

                    return new RedirectResponse($this->admin->generateObjectUrl('acl', $object));
                }
            }
        }

        return $this->render($this->admin->getTemplate('acl'), array(
            'action' => 'acl',
            'permissions' => $adminObjectAclData->getUserPermissions(),
            'object' => $object,
            'users' => $aclUsers,
            'roles' => $aclRoles,
            'aclUsersForm' => $aclUsersForm->createView(),
            'aclRolesForm' => $aclRolesForm->createView(),
        ), null, $request);
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        if ($this->container->has('request_stack')) {
            return $this->container->get('request_stack')->getCurrentRequest();
        }

        return $this->container->get('request');
    }

    /**
     * Render JSON.
     *
     * @param mixed $data
     * @param int   $status
     * @param array $headers
     *
     * @return Response with json encoded data
     */
    protected function renderJson($data, $status = 200, $headers = array())
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
     * Contextualize the admin class depends on the current request.
     *
     * @throws \RuntimeException
     */
    protected function configure()
    {
        $request = $this->getRequest();

        $adminCode = $request->get('_sonata_admin');

        if (!$adminCode) {
            throw new \RuntimeException(sprintf(
                'There is no `_sonata_admin` defined for the controller `%s` and the current route `%s`',
                get_class($this),
                $request->get('_route')
            ));
        }

        $this->admin = $this->container->get('sonata.admin.pool')->getAdminByAdminCode($adminCode);

        if (!$this->admin) {
            throw new \RuntimeException(sprintf(
                'Unable to find the admin class related to the current controller (%s)',
                get_class($this)
            ));
        }

        $rootAdmin = $this->admin;

        if ($this->admin->isChild()) {
            $this->admin->setCurrentChild(true);
            $rootAdmin = $rootAdmin->getParent();
        }

        $rootAdmin->setRequest($request);

        if ($request->get('uniqid')) {
            $this->admin->setUniqid($request->get('uniqid'));
        }
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
            return $this->container->get('logger');
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
        if ($this->isXmlHttpRequest()) {
            return $this->admin->getTemplate('ajax');
        }

        return $this->admin->getTemplate('layout');
    }

    /**
     * @param \Exception $e
     *
     * @throws \Exception
     */
    protected function handleModelManagerException(\Exception $e)
    {
        if ($this->get('kernel')->isDebug()) {
            throw $e;
        }

        $context = array('exception' => $e);
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
        $request = $this->getRequest();

        $url = false;

        if (null !== $request->get('btn_update_and_list')) {
            $url = $this->admin->generateUrl('list');
        }
        if (null !== $request->get('btn_create_and_list')) {
            $url = $this->admin->generateUrl('list');
        }

        if (null !== $request->get('btn_create_and_create')) {
            $params = array();
            if ($this->admin->hasActiveSubClass()) {
                $params['subclass'] = $request->get('subclass');
            }
            $url = $this->admin->generateUrl('create', $params);
        }

        if ($this->getRestMethod() === 'DELETE') {
            $url = $this->admin->generateUrl('list');
        }

        if (!$url) {
            foreach (array('edit', 'show') as $route) {
                if ($this->admin->hasRoute($route) && $this->admin->isGranted(strtoupper($route), $object)) {
                    $url = $this->admin->generateObjectUrl($route, $object);
                    break;
                }
            }
        }

        if (!$url) {
            $url = $this->admin->generateUrl('list');
        }

        return new RedirectResponse($url);
    }

    /**
     * Returns true if the preview is requested to be shown.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isPreviewRequested()
    {
        $request = $this->getRequest();

        return $request->get('btn_preview') !== null;
    }

    /**
     * Returns true if the preview has been approved.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isPreviewApproved()
    {
        $request = $this->getRequest();

        return $request->get('btn_preview_approve') !== null;
    }

    /**
     * Returns true if the request is in the preview workflow.
     *
     * That means either a preview is requested or the preview has already been shown
     * and it got approved/declined.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isInPreviewMode()
    {
        return $this->admin->supportsPreviewMode()
        && ($this->isPreviewRequested()
            || $this->isPreviewApproved()
            || $this->isPreviewDeclined());
    }

    /**
     * Returns true if the preview has been declined.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isPreviewDeclined()
    {
        $request = $this->getRequest();

        return $request->get('btn_preview_decline') !== null;
    }

    /**
     * Gets ACL users.
     *
     * @return \Traversable
     */
    protected function getAclUsers()
    {
        $aclUsers = array();

        $userManagerServiceName = $this->container->getParameter('sonata.admin.security.acl_user_manager');
        if ($userManagerServiceName !== null && $this->has($userManagerServiceName)) {
            $userManager = $this->get($userManagerServiceName);

            if (method_exists($userManager, 'findUsers')) {
                $aclUsers = $userManager->findUsers();
            }
        }

        return is_array($aclUsers) ? new \ArrayIterator($aclUsers) : $aclUsers;
    }

    /**
     * Gets ACL roles.
     *
     * @return \Traversable
     */
    protected function getAclRoles()
    {
        $aclRoles = array();
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

        return is_array($aclRoles) ? new \ArrayIterator($aclRoles) : $aclRoles;
    }

    /**
     * Adds a flash message for type.
     *
     * @param string $type
     * @param string $message
     *
     * @TODO Remove this method when bumping requirements to Symfony >= 2.6
     */
    protected function addFlash($type, $message)
    {
        if (method_exists('Symfony\Bundle\FrameworkBundle\Controller\Controller', 'addFlash')) {
            parent::addFlash($type, $message);
        } else {
            $this->get('session')
                ->getFlashBag()
                ->add($type, $message);
        }
    }

    /**
     * Validate CSRF token for action without form.
     *
     * @param string  $intention
     * @param Request $request
     *
     * @throws HttpException
     */
    protected function validateCsrfToken($intention)
    {
        $request = $this->getRequest();
        $token = $request->request->get('_sonata_csrf_token', false);

        if ($this->container->has('security.csrf.token_manager')) { // SF3.0
            $valid = $this->container->get('security.csrf.token_manager')->isTokenValid(new CsrfToken($intention, $token));
        } elseif ($this->container->has('form.csrf_provider')) { // < SF3.0
            $valid = $this->container->get('form.csrf_provider')->isCsrfTokenValid($intention, $token);
        } else {
            return;
        }

        if (!$valid) {
            throw new HttpException(400, 'The csrf token is not valid, CSRF attack?');
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
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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

        // TODO: Remove it when bumping requirements to SF 2.4+
        if ($this->container->has('form.csrf_provider')) {
            return $this->container->get('form.csrf_provider')->generateCsrfToken($intention);
        }

        return false;
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from createAction.
     *
     * @param Request $request
     * @param mixed   $object
     *
     * @return Response|null
     */
    protected function preCreate(Request $request, $object)
    {
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from editAction.
     *
     * @param Request $request
     * @param mixed   $object
     *
     * @return Response|null
     */
    protected function preEdit(Request $request, $object)
    {
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from deleteAction.
     *
     * @param Request $request
     * @param mixed   $object
     *
     * @return Response|null
     */
    protected function preDelete(Request $request, $object)
    {
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from showAction.
     *
     * @param Request $request
     * @param mixed   $object
     *
     * @return Response|null
     */
    protected function preShow(Request $request, $object)
    {
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from listAction.
     *
     * @param Request $request
     *
     * @return Response|null
     */
    protected function preList(Request $request)
    {
    }

    /**
     * Translate a message id.
     *
     * @param string $id
     * @param array  $parameters
     * @param string $domain
     * @param string $locale
     *
     * @return string translated string
     */
    final protected function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $domain = $domain ?: $this->admin->getTranslationDomain();

        return $this->get('translator')->trans($id, $parameters, $domain, $locale);
    }
}
