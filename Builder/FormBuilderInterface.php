<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder;

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormContextInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Form\FieldFactory\FieldFactoryInterface;

interface FormBuilderInterface
{

    /**
     * @abstract
     * @param \Symfony\Component\Form\FieldFactory\FieldFactoryInterface $fieldFactory
     * @param \Symfony\Component\Form\FormContextInterface $formContext
     * @param \Symfony\Component\Validator\ValidatorInterface $validator
     */
    function __construct(FieldFactoryInterface $fieldFactory, FormContextInterface $formContext, ValidatorInterface $validator);

    /**
     * @abstract
     * @param \Symfony\Component\Form\Form $form
     * @param \Sonata\AdminBundle\Admin\FieldDescription $fieldDescription
     * @return void
     */
    function addField(Form $form, FieldDescriptionInterface $fieldDescription);

    /**
     * @abstract
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param \Sonata\AdminBundle\Admin\FieldDescription $fieldDescription
     * @param array $options
     * @return void
     */
    function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription, array $options = array());

    /**
     * @abstract
     * @param string $name
     * @param object $object
     * @param array $options
     * @return void
     */
    function getBaseForm($name, $object, array $options = array());
}
