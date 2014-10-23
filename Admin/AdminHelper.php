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

use Symfony\Component\Form\FormView;
use Sonata\AdminBundle\Util\FormViewIterator;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Doctrine\Common\Collections\ArrayCollection;

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
        $form = $admin->getFormBuilder()->getForm();
        $form->setData($subject);
        $form->submit($admin->getRequest());

        $elementId = preg_replace('#\.(\d+)#', '[$1]', implode('.', explode('_', substr($elementId, strpos($elementId, '_') + 1))));
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
     * @throws \RuntimeException
     */
    protected function addNewInstance(AdminInterface $admin, $elementId)
    {
        $entity = $admin->getSubject();
        $propertyAccessor = new PropertyAccessor();
        $collection = $propertyAccessor->getValue($entity, $elementId);

        if ($collection instanceof ArrayCollection) {
            $entityClassName = $this->entityClassNameFinder($admin, explode('.', preg_replace('#\[\d*?\]#', '', $elementId)));
        } elseif ($collection instanceof \Doctrine\ORM\PersistentCollection) {
            //since doctrine 2.4
            $entityClassName = $collection->getTypeClass()->getName();
        } else {
            throw new \Exception('unknown collection class');
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
}
