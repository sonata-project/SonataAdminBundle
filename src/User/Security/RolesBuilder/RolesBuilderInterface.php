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

namespace SensioLabs\AdminBundle\User\Security\RolesBuilder;

interface RolesBuilderInterface
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function getRoles(?string $domain = null): array;
}
