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

namespace Sonata\AdminBundle\FieldDescription;

/**
 * @psalm-import-type FieldDescriptionOptions from FieldDescriptionInterface
 */
interface FieldDescriptionFactoryInterface
{
    /**
     * @phpstan-param class-string $class
     * @psalm-param FieldDescriptionOptions $options
     * @phpstan-param array<string, mixed> $options
     */
    public function create(string $class, string $name, array $options = []): FieldDescriptionInterface;
}
