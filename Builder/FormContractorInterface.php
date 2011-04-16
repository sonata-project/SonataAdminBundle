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

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;

interface FormContractorInterface
{

    /**
     * @abstract
     * @param \Symfony\Component\Form\FieldFactory\FormFactoryInterface $formFactory
     * @param \Symfony\Component\Validator\ValidatorInterface $validator
     */
    function __construct(FormFactoryInterface $formFactory, ValidatorInterface $validator);

    /**
     * @abstract
     * @param \Symfony\Component\Form\Form $form
     * @param \Sonata\AdminBundle\Admin\FieldDescription $fieldDescription
     * @return void
     */
    function addField(FormBuilder $form, FieldDescriptionInterface $fieldDescription);

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
     * @param array $options
     * @return void
     */
    function getFormBuilder($name, array $options = array());
}
