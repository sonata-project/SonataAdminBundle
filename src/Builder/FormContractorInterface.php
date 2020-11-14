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

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * This interface should be implemented in persistence bundles.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface FormContractorInterface extends BuilderInterface
{
    public function __construct(FormFactoryInterface $formFactory);

    public function getFormBuilder(string $name, array $formOptions = []): FormBuilderInterface;

    /**
     * Should provide Symfony form options.
     *
     * @return array<string, mixed>
     */
    public function getDefaultOptions(
        ?string $type,
        FieldDescriptionInterface $fieldDescription,
        array $formOptions = []
    ): array;
}
