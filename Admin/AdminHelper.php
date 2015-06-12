<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\AdminBundle\Util\FormViewIterator;
use Sonata\AdminBundle\Util\FormBuilderIterator;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class AdminHelper
 *
 * @package Sonata\AdminBundle\Admin
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AdminHelper
{
    protected $pool;

    /**
     * @param Pool $pool
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @param \Symfony\Component\Form\FormView $formView
     * @param string                           $elementId
     *
     * @return null|\Symfony\Component\Form\FormView
     */
    public function getChildFormView(FormView $formView, $elementId)
    {
        foreach (new \RecursiveIteratorIterator(new FormViewIterator($formView), \RecursiveIteratorIterator::SELF_FIRST) as $name => $formView) {
            if ($name === $elementId) {
                return $formView;
            }
        }

        return null;
    }

    /**
     * @deprecated
     *
     * @param string $code
     *
     * @return \Sonata\AdminBundle\Admin\AdminInterface
     */
    public function getAdmin($code)
    {
        return $this->pool->getInstance($code);
    }

    /**
     * Note:
     *   This code is ugly, but there is no better way of doing it.
     *   For now the append form element action used to add a new row works
     *   only for direct FieldDescription (not nested one)
     *
     * @throws \RuntimeException
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param object                                   $subject
     * @param string                                   $elementId
     *
     * @return array
     */
    public function appendFormFieldElement(AdminInterface $admin, $subject, $elementId)
    {
        // retrieve the subject
        $formBuilder = $admin->getFormBuilder();

        $form = $formBuilder->getForm();
        $form->setData($subject);
        $form->handleRequest($admin->getRequest());

        $elementId = preg_replace('#.(\d+)#', '[$1]', implode('.', explode('_', substr($elementId, strpos($elementId, '_') + 1))));

        // append a new instance into the object
        $this->addNewInstance($admin, $elementId);

        // return new form with empty row
        $finalForm = $admin->getFormBuilder()->getForm();
        $finalForm->setData($subject);
        $finalForm->setData($form->getData());

        return $finalForm;
    }

    /**
     * Add a new instance
     *
     * @param AdminInterface $admin
     * @param string         $elementId
     *
     * @throws \Exception
     */
    public function addNewInstance(AdminInterface $admin, $elementId)
    {
        $entity = $admin->getSubject();

        $propertyAccessor = new PropertyAccessor();

        $collection = $propertyAccessor->getValue($entity, $elementId);

        if ($collection instanceof \Doctrine\ORM\PersistentCollection || $collection instanceof \Doctrine\ODM\MongoDB\PersistentCollection) {
            //since doctrine 2.4
            $entityClassName = $collection->getTypeClass()->getName();
        } elseif ($collection instanceof \Doctrine\Common\Collections\Collection) {
            $entityClassName = $this->entityClassNameFinder($admin, explode('.', preg_replace('#\[\d*?\]#', '', $elementId)));
        } else {
            return;
        }

        if (!method_exists($collection, 'add')){
            return;
        }

        $collection->add(new $entityClassName);

        $propertyAccessor->setValue($entity, $elementId, $collection);
    }


    protected function entityClassNameFinder(AdminInterface $admin, $elements)
    {
        $element = array_shift($elements);

        $associationAdmin = $admin->getFormFieldDescription($element)->getAssociationAdmin();

        if (count($elements) == 0) {
            return $associationAdmin->getClass();
        } else {
            return $this->entityClassNameFinder($associationAdmin, $elements);
        }
    }

    /**
     * Camelize a string
     *
     * @static
     *
     * @param string $property
     *
     * @return string
     */
    public function camelize($property)
    {
        return BaseFieldDescription::camelize($property);
    }
}
