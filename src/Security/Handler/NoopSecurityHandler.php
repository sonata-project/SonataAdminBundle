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

namespace SensioLabs\AdminBundle\Security\Handler;

use SensioLabs\AdminBundle\Admin\AdminInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class NoopSecurityHandler implements SecurityHandlerInterface
{
    public function isGranted(AdminInterface $admin, string $attribute, ?object $object = null): bool
    {
        return true;
    }

    public function getBaseRole(AdminInterface $admin): string
    {
        return '';
    }

    public function buildSecurityInformation(AdminInterface $admin): array
    {
        return [];
    }

    public function createObjectSecurity(AdminInterface $admin, object $object): void
    {
    }

    public function deleteObjectSecurity(AdminInterface $admin, object $object): void
    {
    }
}
