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

/**
 * This interface should be implemented in persistence bundles.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface FormContractorInterface extends BuilderInterface
{
    /**
     * @param array<string, mixed> $formOptions
     */
    public function getFormBuilder(string $name, array $formOptions = []): FormBuilderInterface;

    /**
     * @param array<string, mixed> $formOptions
     *
     * @return array<string, mixed>
     *
     * @phpstan-param class-string|null $type
     */
    public function getDefaultOptions(
        ?string $type,
        FieldDescriptionInterface $fieldDescription,
        array $formOptions = []
    ): array;
}
