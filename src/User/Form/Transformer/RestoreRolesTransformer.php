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

namespace SensioLabs\AdminBundle\User\Form\Transformer;

use SensioLabs\AdminBundle\User\Security\EditableRolesBuilder;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Preserves hidden roles during form submission by merging back
 * roles the current user cannot see/edit.
 *
 * @implements DataTransformerInterface<list<string>, list<string>>
 */
final class RestoreRolesTransformer implements DataTransformerInterface
{
    /**
     * @var list<string>|null
     */
    private ?array $originalRoles = null;

    public function __construct(
        private readonly EditableRolesBuilder $rolesBuilder,
    ) {
    }

    /**
     * @param list<string>|null $value
     */
    public function setOriginalRoles(?array $value): void
    {
        $this->originalRoles = $value ?? [];
    }

    /**
     * @param list<string>|null $value
     *
     * @return list<string>
     */
    public function transform(mixed $value): array
    {
        if (null === $value) {
            return [];
        }

        return $value;
    }

    /**
     * @param list<string>|null $value
     *
     * @return list<string>
     */
    public function reverseTransform(mixed $value): array
    {
        $value ??= [];

        $readOnlyRoles = $this->rolesBuilder->getRolesReadOnly();

        // Merge back the roles the user cannot edit
        $hiddenRoles = array_intersect($this->originalRoles ?? [], $readOnlyRoles);

        return array_values(array_unique(array_merge($value, $hiddenRoles)));
    }
}
