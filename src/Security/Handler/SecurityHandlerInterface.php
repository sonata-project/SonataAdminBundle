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
     * @param string|array $attributes
     * @param mixed|null   $object
     */
    public function isGranted(AdminInterface $admin, $attributes, $object = null): bool;

    /**
     * Get a sprintf template to get the role.
     */
    public function getBaseRole(AdminInterface $admin): string;

    public function buildSecurityInformation(AdminInterface $admin);

    /**
     * Create object security, fe. make the current user owner of the object.
     *
     * @param mixed $object
     */
    public function createObjectSecurity(AdminInterface $admin, $object);

    /**
     * Remove object security.
     *
     * @param mixed $object
     */
    public function deleteObjectSecurity(AdminInterface $admin, $object);
}
