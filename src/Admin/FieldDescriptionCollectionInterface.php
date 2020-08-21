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

namespace Sonata\AdminBundle\Admin;

interface FieldDescriptionCollectionInterface extends \ArrayAccess, \Countable
{
    public function add(FieldDescriptionInterface $fieldDescription): void;

    /**
     * @return array<string, FieldDescriptionInterface>
     */
    public function getElements(): array;

    public function has(string $name): bool;

    /**
     * @throws \InvalidArgumentException if the element is not found
     */
    public function get(string $name): FieldDescriptionInterface;

    public function remove(string $name): void;

    /**
     * @param array<string> $keys
     */
    public function reorder(array $keys): void;
}
