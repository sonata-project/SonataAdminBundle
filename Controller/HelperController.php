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

class HelperController extends Controller
{

    /**
     * @return \Sonata\AdminBundle\Admin\AdminHelper
     */
    public function getAdminHelper()
    {
        return $this->container->get('sonata.admin.helper');
    }

    public function appendFormFieldElementAction()
    {
        $helper     = $this->getAdminHelper();
        $request    = $this->get('request');
        $code       = $request->get('code');
        $elementId  = $request->get('elementId');
        $objectId   = $request->get('objectId');

        $admin = $helper->getAdmin($code);

        $subject = $admin->getModelManager()->findOne($admin->getClass(), $objectId);
        if (!$subject) {
            $subject = $admin->getNewInstance();
        }

        $admin->setSubject($subject);
        $admin->setRequest($request);

        list($fieldDescription, $formBuilder) = $helper->appendFormFieldElement($admin, $elementId);

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...
        $twig = $this->get('twig');
        $extension = $twig->getExtension('sonata_admin');
        $extension->initRuntime($this->get('twig'));

        return new Response($extension->renderFormElement($fieldDescription, $formBuilder->getForm()->createView(), $formBuilder->getData()));
    }

    public function retrieveFormFieldElementAction()
    {
        $helper     = $this->getAdminHelper();
        $code       = $this->get('request')->get('code');
        $elementId  = $this->get('request')->get('elementId');
        $objectId   = $this->get('request')->get('objectId');
        $admin      = $helper->getAdmin($code);
        $uniqid     = $this->get('request')->query->get('uniqid');

        $subject = $admin->getModelManager()->findOne($admin->getClass(), $objectId);
        if (!$subject) {
            throw new NotFoundHttpException(sprintf('Unable to find the object id: %s, class: %s', $objectId, $admin->getClass()));
        }
        if ($uniqid) {
            $admin->setUniqid($uniqid);
        }

        $formBuilder = $admin->getFormBuilder($subject);

        $form = $formBuilder->getForm();
        $form->bindRequest($this->get('request'));

        $childFormBuilder = $helper->getChildFormBuilder($formBuilder, $elementId);

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...
        $twig = $this->get('twig');
        $extension = $twig->getExtension('form');
        $extension->initRuntime($this->get('twig'));

        return new Response($extension->renderWidget($childFormBuilder->getForm()->createView()));
    }

    public function getShortObjectDescriptionAction($code = null, $objectId = null, $uniqid = null)
    {
        $code       = $code     ?: $this->get('request')->query->get('code');
        $objectId   = $objectId ?: $this->get('request')->query->get('objectId');
        $uniqid     = $uniqid   ?: $this->get('request')->get('uniqid');

        $admin  = $this->container->get('sonata.admin.pool')->getInstance($code);
        if ($uniqid) {
            $admin->setUniqid($uniqid);
        }

        $object = $admin->getObject($objectId);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $objectId));
        }

        $description = 'no description available';
        foreach (array('getTitle', 'getName', '__toString') as $method) {
            if (method_exists($object, $method)) {
                $description = $object->$method();
                break;
            }
        }

        $description = sprintf('<a href="%s" target="new">%s</a>', $admin->generateUrl('edit', array('id' => $objectId)), $description);

        return new Response($description);
    }
}