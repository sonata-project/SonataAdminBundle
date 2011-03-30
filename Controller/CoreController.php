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

use Sonata\AdminBundle\Form\RecursiveFieldIterator;

class CoreController extends Controller
{

    public function getBaseTemplate()
    {        
        if ($this->get('request')->isXmlHttpRequest()) {
            return $this->container->getParameter('sonata_admin.templates.ajax');
        }

        return $this->container->getParameter('sonata_admin.templates.layout');
    }

    public function retrieveFormFieldElementAction()
    {
        $code = $this->get('request')->get('code');
        $elementId = $this->get('request')->get('elementId');

        $admin = $this->getAdmin($code);
        
        $form = $this->getForm($admin, $code);

        $form->bind($this->get('request'));
        
        $field_element = $this->getFieldElement($form, $elementId);

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...
        $twig = $this->get('twig');
        $extension = $twig->getExtension('form');
        $extension->initRuntime($this->get('twig'));

        return new Response($extension->renderField($field_element));
    }

    public function getAdmin($code)
    {
        // todo : refactor the code into inside the admin
        $admin = $this->container
           ->get('sonata_admin.admin.pool')
           ->getInstance($code);

        $admin->setRequest($this->container->get('request'));
        
        return $admin;
    }
    
    public function getForm($admin, $code)
    {

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
        $admin            = $this->getAdmin($code);
        
        // retrieve the subject
        $form = $this->getForm($admin, $code);

        // get the field element
        $field_element = $this->getFieldElement($form, $elementId);

        // retrieve the FieldDescription
        $fieldDescription       = $admin->getFormFieldDescription($field_element->getKey());

        $subject = $form->getData();
        $value = $fieldDescription->getValue($subject);

        // retrieve the posted data
        $data = $this->get('request')->get($form->getName());

        if (!isset($data[$field_element->getKey()])) {
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
        $extension = $twig->getExtension('sonata_admin');
        $extension->initRuntime($this->get('twig'));

        return new Response($extension->renderFormElement($fieldDescription, $form, $form->getData()));
    }

    public function getShortObjectDescriptionAction($code = null, $objectId = null, $uniqid = null)
    {

        $code       = $code     ?: $this->get('request')->query->get('code');
        $objectId   = $objectId ?: $this->get('request')->query->get('objectId');
        $uniqid     = $uniqid   ?: $this->get('request')->get('uniqid');

        $admin  = $this->container->get('sonata_admin.admin.pool')->getInstance($code);
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

    public function dashboardAction()
    {

        return $this->render('SonataAdmin:Core:dashboard.html.twig', array(
            'groups' => $this->get('sonata_admin.admin.pool')->getDashboardGroups(),
            'base_template'  => $this->getBaseTemplate(),
        ));
    }
}