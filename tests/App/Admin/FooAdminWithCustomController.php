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

namespace SensioLabs\AdminBundle\Tests\App\Admin;

final class FooAdminWithCustomController extends FooAdmin
{
    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'tests/app/foo-with-custom-controller';
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'admin_foo_with_custom_controller';
    }
}
