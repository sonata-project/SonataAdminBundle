<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder;

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * This interface should be implemented in persistence bundles.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface FormContractorInterface extends BuilderInterface
{
    /**
     * @abstract
     *
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory);

    /**
     * @abstract
     *
     * @param string $name
     * @param array  $options
     *
     * @return FormBuilder
     */
    public function getFormBuilder($name, array $options = array());

    /**
     * Should provide Symfony form options.
     *
     * @abstract
     *
     * @param string                    $type
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @return array
     */
    public function getDefaultOptions($type, FieldDescriptionInterface $fieldDescription);
}
