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

class HelperController extends Controller
{
    /**
     * @return \Sonata\AdminBundle\Admin\AdminHelper
     */
    public function getAdminHelper()
    {
        return $this->container->get('sonata.admin.helper');
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function appendFormFieldElementAction()
    {
        $helper     = $this->getAdminHelper();
        $request    = $this->get('request');
        $code       = $request->query->get('code');
        $elementId  = $request->query->get('elementId');
        $objectId   = $request->query->get('objectId');
        $uniqid     = $this->get('request')->query->get('uniqid');

        $admin = $helper->getAdmin($code);
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
        $admin->setRequest($request);

        list($fieldDescription, $form) = $helper->appendFormFieldElement($admin, $elementId);

        $view = $helper->getChildFormView($form->createView(), $elementId);

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...
        $twig = $this->get('twig');
        $extension = $twig->getExtension('form');
        $extension->initRuntime($this->get('twig'));
        $extension->setTheme($view, $admin->getFormTheme());

        return new Response($extension->renderWidget($view));
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function retrieveFormFieldElementAction()
    {
        $helper     = $this->getAdminHelper();
        $code       = $this->get('request')->query->get('code');
        $elementId  = $this->get('request')->query->get('elementId');
        $objectId   = $this->get('request')->query->get('objectId');
        $admin      = $helper->getAdmin($code);
        $uniqid     = $this->get('request')->query->get('uniqid');

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

        $formBuilder = $admin->getFormBuilder($subject);

        $form = $formBuilder->getForm();
        $form->setData($subject);

        $view = $helper->getChildFormView($form->createView(), $elementId);

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...
        $twig = $this->get('twig');
        $extension = $twig->getExtension('form');
        $extension->initRuntime($this->get('twig'));
        $extension->setTheme($view, $admin->getFormTheme());

        return new Response($extension->renderWidget($view));
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getShortObjectDescriptionAction()
    {
        $code       = $this->get('request')->query->get('code');
        $objectId   = $this->get('request')->query->get('objectId');
        $uniqid     = $this->get('request')->query->get('uniqid');

        $admin  = $this->container->get('sonata.admin.pool')->getInstance($code);
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
                $description = $object->$method();
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
        $field      = $this->get('request')->query->get('field');
        $code       = $this->get('request')->query->get('code');
        $objectId   = $this->get('request')->query->get('objectId');
        $uniqid     = $this->get('request')->query->get('uniqid');
        $value      = $this->get('request')->query->get('value');
        $context    = $this->get('request')->query->get('context');

        $admin  = $this->container->get('sonata.admin.pool')->getInstance($code);

        // alter should be done by using a post method
        if ($this->getRequest()->getMethod() != 'POST') {
            return new Response(json_encode(array('status' => 'KO', 'message' => 'Expected a POST Request')), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        // check user permission
        if (false === $admin->isGranted('EDIT')) {
            return new Response(json_encode(array('status' => 'KO', 'message' => 'Invalid permissions')), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        if ($uniqid) {
            $admin->setUniqid($uniqid);
        }

        $object = $admin->getObject($objectId);

        if (!$object) {
            return new Response(json_encode(array('status' => 'KO', 'message' => 'Object does not exist')), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        if ($context == 'list') {
            $fieldDescription = $admin->getListFieldDescription($field);
        } else if ($context == 'show') {
            $fieldDescription = $admin->getShowFieldDescription($field);
        } else {
            return new Response(json_encode(array('status' => 'KO', 'message' => 'Invalid context')), 200, array(
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
        $twig = $this->get('twig');
        $extension = $twig->getExtension('sonata_admin');
        $extension->initRuntime($this->get('twig'));

        $content = $extension->renderListElement($object, $fieldDescription);

        return new Response(json_encode(array('status' => 'OK', 'content' => $content)), 200, array(
            'Content-Type' => 'application/json'
        ));
    }
}