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

/**
 * Tells if the current user has access to a given action.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface AccessRegistryInterface
{
    /**
     * Return the controller access mapping.
     *
     * @return array<string, string[]|string>
     */
    public function getAccessMapping(): array;

    /**
     * Hook to handle access authorization.
     */
    public function checkAccess(string $action, ?object $object = null): void;

    /**
     * Hook to handle access authorization, without throwing an exception.
     */
    public function hasAccess(string $action, ?object $object = null): bool;
}
