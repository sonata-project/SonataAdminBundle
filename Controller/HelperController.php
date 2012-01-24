<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\HttpFoundation\Request;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Admin\AdminHelper;

class HelperController
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Sonata\AdminBundle\Admin\AdminHelper
     */
    protected $helper;

    /**
     * @var \Sonata\AdminBundle\Admin\Pool
     */
    protected $pool;

    /**
     * @param \Twig_Environment $twig
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Sonata\AdminBundle\Admin\Pool $pool
     * @param \Sonata\AdminBundle\Admin\AdminHelper $helper
     */
    public function __construct(\Twig_Environment $twig, Request $request, Pool $pool, AdminHelper $helper)
    {
        $this->twig     = $twig;
        $this->request  = $request;
        $this->pool     = $pool;
        $this->helper   = $helper;
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function appendFormFieldElementAction()
    {
        $code       = $this->request->get('code');
        $elementId  = $this->request->get('elementId');
        $objectId   = $this->request->get('objectId');
        $uniqid     = $this->request->get('uniqid');

        $admin      = $this->pool->getInstance($code);

        if ($uniqid) {
            $admin->setUniqid($uniqid);
        }

        $subject = $admin->getModelManager()->find($admin->getClass(), $objectId);
        if ($objectId && !$subject) {
            throw new NotFoundHttpException;
        }

        if (!$subject) {
            $subject = $admin->getNewInstance();
        }

        $admin->setSubject($subject);
        $admin->setRequest($this->request);

        list($fieldDescription, $form) = $this->helper->appendFormFieldElement($admin, $elementId);

        $view = $this->helper->getChildFormView($form->createView(), $elementId);

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...

        $extension = $this->twig->getExtension('form');
        $extension->initRuntime($this->twig);
        $extension->setTheme($view, $admin->getFormTheme());

        return new Response($extension->renderWidget($view));
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function retrieveFormFieldElementAction()
    {

        $code       = $this->request->get('code');
        $elementId  = $this->request->get('elementId');
        $objectId   = $this->request->get('objectId');
        $uniqid     = $this->request->get('uniqid');

        $admin       = $this->pool->getInstance($code);

        if ($objectId) {
            $subject = $admin->getModelManager()->find($admin->getClass(), $objectId);
            if (!$subject) {
                throw new NotFoundHttpException(sprintf('Unable to find the object id: %s, class: %s', $objectId, $admin->getClass()));
            }
        } else {
            $subject = $admin->getNewInstance();
        }

        if ($uniqid) {
            $admin->setUniqid($uniqid);
        }

        $admin->setSubject($subject);
        
        $formBuilder = $admin->getFormBuilder($subject);

        $form = $formBuilder->getForm();
        $form->bindRequest($this->request);

        $view = $this->helper->getChildFormView($form->createView(), $elementId);

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...
        $extension = $this->twig->getExtension('form');
        $extension->initRuntime($this->twig);
        $extension->setTheme($view, $admin->getFormTheme());

        return new Response($extension->renderWidget($view));
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getShortObjectDescriptionAction()
    {
        $code       = $this->request->get('code');
        $objectId   = $this->request->get('objectId');
        $uniqid     = $this->request->get('uniqid');

        $admin       = $this->pool->getInstance($code);

        if (!$admin) {
            throw new NotFoundHttpException();
        }

        if ($uniqid) {
            $admin->setUniqid($uniqid);
        }

        $object = $admin->getObject($objectId);

        if (!$object) {
            return new Response();
        }

        $description = 'no description available';
        foreach (array('getAdminTitle', 'getTitle', 'getName', '__toString') as $method) {
            if (method_exists($object, $method)) {
                $description = call_user_func(array($object, $method));
                break;
            }
        }

        $description = sprintf('<a href="%s" target="new">%s</a>', $admin->generateUrl('edit', array('id' => $objectId)), $description);

        return new Response($description);
    }

    /**
     * Toggle boolean value of property in list
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setObjectFieldValueAction()
    {
        $field      = $this->request->get('field');
        $code       = $this->request->get('code');
        $objectId   = $this->request->get('objectId');
        $value      = $this->request->get('value');
        $context    = $this->request->get('context');

        $admin       = $this->pool->getInstance($code);

        // alter should be done by using a post method
        if ($this->request->getMethod() != 'POST') {
            return new Response(json_encode(array('status' => 'KO', 'message' => 'Expected a POST Request')), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        $object = $admin->getObject($objectId);

        if (!$object) {
            return new Response(json_encode(array('status' => 'KO', 'message' => 'Object does not exist')), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        // check user permission
        if (false === $admin->isGranted('EDIT', $object)) {
            return new Response(json_encode(array('status' => 'KO', 'message' => 'Invalid permissions')), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        if ($context == 'list') {
            $fieldDescription = $admin->getListFieldDescription($field);
        } else {
            return new Response(json_encode(array('status' => 'KO', 'message' => 'Invalid context')), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        if (!$fieldDescription) {
            return new Response(json_encode(array('status' => 'KO', 'message' => 'The field does not exist')), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        if (!$fieldDescription->getOption('editable')) {
            return new Response(json_encode(array('status' => 'KO', 'message' => 'The field cannot be edit, editable option must be set to true')), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        // TODO : call the validator component ...
        $propertyPath = new PropertyPath($field);
        $propertyPath->setValue($object, $value);

        $admin->update($object);

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...
        $extension = $this->twig->getExtension('sonata_admin');
        $extension->initRuntime($this->twig);

        $content = $extension->renderListElement($object, $fieldDescription);

        return new Response(json_encode(array('status' => 'OK', 'content' => $content)), 200, array(
            'Content-Type' => 'application/json'
        ));
    }
}