<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BaseApplicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sonata\BaseApplicationBundle\Form\RecursiveFieldIterator;

class CoreController extends Controller
{
    public function getBaseTemplate()
    {        
        if ($this->get('request')->isXmlHttpRequest()) {
            return $this->container->getParameter('base_application.templates.ajax');
        }

        return $this->container->getParameter('base_application.templates.layout');
    }

    public function retrieveFormFieldElementAction()
    {
        $code = $this->get('request')->get('code');
        $elementId = $this->get('request')->get('elementId');
        
        $form = $this->getForm($code);

        $form->bind($this->get('request'));
        
        $field_element = $this->getFieldElement($form, $elementId);

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...
        $twig = $this->get('twig');
        $extension = $twig->getExtension('form');
        $extension->initRuntime($this->get('twig'));

        return $this->createResponse($extension->renderField($field_element));
    }

    public function getForm($code)
    {
        // todo : refactor the code into inside the admin
        $admin = $this->container
           ->get('base_application.admin.pool')
           ->getInstance($code);

        if (is_numeric($this->get('request')->get('object_id'))) {
            $object = $admin->getObject($this->get('request')->get('object_id'));
        } else {
            $class = $admin->getClass();
            $object = new $class;
        }

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : `%s`', $this->get('request')->get('object_id')));
        }

        return $admin->getForm($object);
    }

    public function getFieldElement($form, $element_id)
    {
        $iterator = new RecursiveFieldIterator($form);
        $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);

        $field_element = false;
        foreach ($iterator as $field) {

            if ($field->getId() == $element_id) {
                // find the targeted element
                return $field;
            }
        }

        if (!$field_element) {
            throw new NotFoundHttpException(sprintf('unable to retrieve the form field element with id : `%s`', $element_id));
        }
    }
    
    public function appendFormFieldElementAction()
    {

        $code = $this->get('request')->get('code');
        $elementId = $this->get('request')->get('elementId');

        // Note : This code is ugly, I guess there is a better way of doing it.
        //        For now the append form element action used to add a new row works
        //        only for direct FieldDescription (not nested one)

        // retrieve the admin
        $admin            = $this->container->get('base_application.admin.pool')->getInstance($code);
        
        // retrieve the subject
        $form = $this->getForm($code);

        // get the field element
        $field_element = $this->getFieldElement($form, $elementId);

        // retrieve the FieldDescription
        $fieldDescription       = $admin->getFormFieldDescription($field_element->getKey());

        $subject = $form->getData();
        $value = $fieldDescription->getValue($subject);

        // retrieve the posted data
        $data = $this->get('request')->get($form->getName());

        if(!isset($data[$field_element->getKey()])) {
            $data[$field_element->getKey()] = array();
        }

        $object_count   = count($value);
        $post_count     = count($data[$field_element->getKey()]);

        // for now, not sure how to do that
        $value = array();
        foreach ($field_element->getPrototype()->getFields() as $name => $t) {
            $value[$name] = '';
        }

        // add new elements to the subject
        while($object_count < $post_count) {
            // append a new instance into the object
            $admin->getFormBuilder()->addNewInstance($subject, $fieldDescription);

            $object_count++;
        }

        $admin->getFormBuilder()->addNewInstance($subject, $fieldDescription);
        $data[$field_element->getKey()][] = $value;

        $form   = $admin->getForm($subject);

        // bind the data
        $form->submit($data);

        $admin->setSubject($subject);
        
        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...
        $twig = $this->get('twig');
        $extension = $twig->getExtension('base_application');
        $extension->initRuntime($this->get('twig'));

        return $this->createResponse($extension->renderFormElement($fieldDescription, $form, $form->getData()));
    }

    public function getShortObjectDescriptionAction()
    {

        $code = $this->get('request')->query->get('code');
        $objectId = $this->get('request')->query->get('objectId');
        
        $admin  = $this->container->get('base_application.admin.pool')->getInstance($code);

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

        return $this->createResponse($description);
    }

    public function dashboardAction()
    {

        return $this->render('SonataBaseApplicationBundle:Core:dashboard.twig.html', array(
            'groups' => $this->get('base_application.admin.pool')->getGroups(),
            'base_template'  => $this->getBaseTemplate(),
        ));
    }
}