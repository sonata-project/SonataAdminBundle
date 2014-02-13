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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ValidatorInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Admin\AdminHelper;

class HelperController
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var \Sonata\AdminBundle\Admin\AdminHelper
     */
    protected $helper;

    /**
     * @var \Sonata\AdminBundle\Admin\Pool
     */
    protected $pool;

    /**
     * @var \Symfony\Component\Validator\ValidatorInterface
     */
    protected $validator;

    /**
     * @param \Twig_Environment                               $twig
     * @param \Sonata\AdminBundle\Admin\Pool                  $pool
     * @param \Sonata\AdminBundle\Admin\AdminHelper           $helper
     * @param \Symfony\Component\Validator\ValidatorInterface $validator
     */
    public function __construct(\Twig_Environment $twig, Pool $pool, AdminHelper $helper, ValidatorInterface $validator)
    {
        $this->twig      = $twig;
        $this->pool      = $pool;
        $this->helper    = $helper;
        $this->validator = $validator;
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function appendFormFieldElementAction(Request $request)
    {
        $code      = $request->get('code');
        $elementId = $request->get('elementId');
        $objectId  = $request->get('objectId');
        $uniqid    = $request->get('uniqid');

        $admin = $this->pool->getInstance($code);
        $admin->setRequest($request);

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

        list($fieldDescription, $form) = $this->helper->appendFormFieldElement($admin, $subject, $elementId);

        /** @var $form \Symfony\Component\Form\Form */
        $view = $this->helper->getChildFormView($form->createView(), $elementId);

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...

        $extension = $this->twig->getExtension('form');
        $extension->initRuntime($this->twig);
        $extension->renderer->setTheme($view, $admin->getFormTheme());

        return new Response($extension->renderer->searchAndRenderBlock($view, 'widget'));
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function retrieveFormFieldElementAction(Request $request)
    {
        $code      = $request->get('code');
        $elementId = $request->get('elementId');
        $objectId  = $request->get('objectId');
        $uniqid    = $request->get('uniqid');

        $admin = $this->pool->getInstance($code);
        $admin->setRequest($request);

        if ($uniqid) {
            $admin->setUniqid($uniqid);
        }

        if ($objectId) {
            $subject = $admin->getModelManager()->find($admin->getClass(), $objectId);
            if (!$subject) {
                throw new NotFoundHttpException(sprintf('Unable to find the object id: %s, class: %s', $objectId, $admin->getClass()));
            }
        } else {
            $subject = $admin->getNewInstance();
        }

        $admin->setSubject($subject);

        $formBuilder = $admin->getFormBuilder($subject);

        $form = $formBuilder->getForm();
        $form->submit($request);

        $view = $this->helper->getChildFormView($form->createView(), $elementId);

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...
        $extension = $this->twig->getExtension('form');
        $extension->initRuntime($this->twig);
        $extension->renderer->setTheme($view, $admin->getFormTheme());

        return new Response($extension->renderer->searchAndRenderBlock($view, 'widget'));
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException|\RuntimeException
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getShortObjectDescriptionAction(Request $request)
    {
        $code           = $request->get('code');
        $objectId       = $request->get('objectId');
        $uniqid         = $request->get('uniqid');
        $linkParameters = $request->get('linkParameters', array());

        $admin = $this->pool->getInstance($code);

        if (!$admin) {
            throw new NotFoundHttpException();
        }

        $admin->setRequest($request);

        if ($uniqid) {
            $admin->setUniqid($uniqid);
        }

        $object = $admin->getObject($objectId);

        if (!$object && 'html' == $request->get('_format')) {
            return new Response();
        }

        if ('json' == $request->get('_format')) {
            return new JsonResponse(array('result' => array(
                'id'    => $admin->id($object),
                'label' => $admin->toString($object)
            )));
        } elseif ('html' == $request->get('_format')) {
            return new Response($this->twig->render($admin->getTemplate('short_object_description'), array(
                'admin'           => $admin,
                'description'     => $admin->toString($object),
                'object'          => $object,
                'link_parameters' => $linkParameters
            )));
        } else {
            throw new \RuntimeException('Invalid format');
        }
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setObjectFieldValueAction(Request $request)
    {
        $field    = $request->get('field');
        $code     = $request->get('code');
        $objectId = $request->get('objectId');
        $value    = $request->get('value');
        $context  = $request->get('context');

        $admin = $this->pool->getInstance($code);
        $admin->setRequest($request);

        // alter should be done by using a post method
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array('status' => 'KO', 'message' => 'Expected a XmlHttpRequest request header'));
        }

        if ($request->getMethod() != 'POST') {
            return new JsonResponse(array('status' => 'KO', 'message' => 'Expected a POST Request'));
        }

        $rootObject = $object = $admin->getObject($objectId);

        if (!$object) {
            return new JsonResponse(array('status' => 'KO', 'message' => 'Object does not exist'));
        }

        // check user permission
        if (false === $admin->isGranted('EDIT', $object)) {
            return new JsonResponse(array('status' => 'KO', 'message' => 'Invalid permissions'));
        }

        if ($context == 'list') {
            $fieldDescription = $admin->getListFieldDescription($field);
        } else {
            return new JsonResponse(array('status' => 'KO', 'message' => 'Invalid context'));
        }

        if (!$fieldDescription) {
            return new JsonResponse(array('status' => 'KO', 'message' => 'The field does not exist'));
        }

        if (!$fieldDescription->getOption('editable')) {
            return new JsonResponse(array('status' => 'KO', 'message' => 'The field cannot be edit, editable option must be set to true'));
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $propertyPath     = new PropertyPath($field);

        // If property path has more than 1 element, take the last object in order to validate it
        if ($propertyPath->getLength() > 1) {
            $object = $propertyAccessor->getValue($object, $propertyPath->getParent());

            $elements     = $propertyPath->getElements();
            $field        = end($elements);
            $propertyPath = new PropertyPath($field);
        }

        $propertyAccessor->setValue($object, $propertyPath, '' !== $value ? $value : null);

        $violations = $this->validator->validateProperty($object, $field);

        if (count($violations)) {
            $messages = array();

            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }

            return new JsonResponse(array('status' => 'KO', 'message' => implode("\n", $messages)));
        }

        $admin->update($object);

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...
        $extension = $this->twig->getExtension('sonata_admin');
        $extension->initRuntime($this->twig);

        $content = $extension->renderListElement($rootObject, $fieldDescription);

        return new JsonResponse(array('status' => 'OK', 'content' => $content));
    }
}
