<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder;

use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * This interface should be implemented in persistence bundles.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface FormContractorInterface extends BuilderInterface
{
    /**
     * NEXT_MAJOR: Remove the `__construct()` method from the interface.
     */
    public function __construct(FormFactoryInterface $formFactory);

    /**
     * @param string               $name
     * @param array<string, mixed> $formOptions
     *
     * @return FormBuilderInterface
     */
    public function getFormBuilder($name, array $formOptions = []);

    /**
     * NEXT_MAJOR: Change signature to add the third parameter array<string, mixed> $formOptions.
     *
     * Should provide Symfony form options.
     *
     * @param string|null $type
     *
     * @return array<string, mixed>
     */
    public function getDefaultOptions($type, FieldDescriptionInterface $fieldDescription/*, array $formOptions = []*/);
}

// NEXT_MAJOR: Remove next line.
interface_exists(FieldDescriptionInterface::class);
