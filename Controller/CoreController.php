<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\Sonata\BaseApplicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Form\Iterator\RecursiveFieldsWithPropertyPathIterator;

class CoreController extends Controller
{

    public function retrieveFormFieldElementAction($code, $element_id)
    {

        // todo : refactor the code into inside the admin
        $admin = $this->container
           ->get('base_application.admin.pool')
           ->getInstance($code);

        if(is_numeric($this->get('request')->get('object_id'))) {
            $object = $this->configuration->getObject($this->get('request')->get('object_id'));
        } else {
            $class = $admin->getClass();
            $object = new $class;
        }

        if(!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : `%s`', $this->get('request')->get('object_id')));
        }

        $fields = $admin->getFormFields();
        $form   = $admin->getForm($object, $fields);

        // bind the form so the form element will be populated with the lastest elements
        $form->bind($this->get('request')->get('data'));

        $iterator = new RecursiveFieldsWithPropertyPathIterator($form);
        $iterator = new \RecursiveIteratorIterator($iterator);

        $field_element = false;
        foreach ($iterator as $field) {

            if($field->getId() == $element_id) {
                // find the targeted element
                $field_element = $field;
            }
        }

        if(!$field_element) {
            throw new NotFoundHttpException(sprintf('unable to retrieve the form field element with id : `%s`', $element_id));
        }

//        $key = $field_element->getKey();
//
//        if(!isset($fields[$key])) {
//            throw new NotFoundHttpException(sprintf('unable to retrieve the form field description with key : %s', $key));
//        }
//
//        $field_description = $fields[$key];

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...
        $twig = $this->get('twig');
        $extension = $twig->getExtension('form');
        $extension->initRuntime($this->get('twig'));

        return $this->createResponse($extension->render($field_element));


//        return $this->render($field_description['template'], array(
//            'configuration'     => $admin,
//            'field_description' => $field_description,
//            'object'            => $object,
//            'field_element'     => $field_element,
//        ));
    }

    public function dashboardAction()
    {

        return $this->render('BaseApplicationBundle:Core:dashboard.twig', array(
            'groups' => $this->get('base_application.admin.pool')->getGroups()
        ));
    }
}