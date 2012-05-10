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

interface FormContractorInterface
{

    /**
     * @abstract
     *
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     */
    function __construct(FormFactoryInterface $formFactory);

    /**
     * @abstract
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface            $admin
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     *
     * @return void
     */
    function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription);

    /**
     * @abstract
     *
     * @param string $name
     * @param array  $options
     *
     * @return FormBuilder
     */
    function getFormBuilder($name, array $options = array());

    /**
     * @abstract
     *
     * @param string                                              $type
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     *
     * @return array
     */
    function getDefaultOptions($type, FieldDescriptionInterface $fieldDescription);
}
