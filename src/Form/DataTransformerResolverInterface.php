<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Form;

use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;
use SensioLabs\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author Peter Gribanov <info@peter-gribanov.ru>
 */
interface DataTransformerResolverInterface
{
    /**
     * @phpstan-param DataTransformerInterface<mixed, mixed> $dataTransformer
     * @psalm-param DataTransformerInterface $dataTransformer
     */
    public function addCustomGlobalTransformer(string $fieldType, DataTransformerInterface $dataTransformer): void;

    /**
     * @param ModelManagerInterface<object> $modelManager
     *
     * @phpstan-return DataTransformerInterface<mixed, mixed>|null
     * @psalm-return DataTransformerInterface|null
     */
    public function resolve(
        FieldDescriptionInterface $fieldDescription,
        ModelManagerInterface $modelManager,
    ): ?DataTransformerInterface;
}
