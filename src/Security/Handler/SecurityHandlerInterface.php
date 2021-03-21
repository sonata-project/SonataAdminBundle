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

namespace Sonata\AdminBundle\Security\Handler;

use Sonata\AdminBundle\Admin\AdminInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface SecurityHandlerInterface
{
    /**
     * @param AdminInterface<object> $admin
     * @param string|string[]        $attributes
     */
    public function isGranted(AdminInterface $admin, $attributes, ?object $object = null): bool;

    /**
     * Get a sprintf template to get the role.
     *
     * @param AdminInterface<object> $admin
     */
    public function getBaseRole(AdminInterface $admin): string;

    /**
     * @param AdminInterface<object> $admin
     *
     * @return array<string, string[]>
     */
    public function buildSecurityInformation(AdminInterface $admin): array;

    /**
     * Create object security, fe. make the current user owner of the object.
     *
     * @param AdminInterface<object> $admin
     */
    public function createObjectSecurity(AdminInterface $admin, object $object): void;

    /**
     * Remove object security.
     *
     * @param AdminInterface<object> $admin
     */
    public function deleteObjectSecurity(AdminInterface $admin, object $object): void;
}
