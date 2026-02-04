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

namespace SensioLabs\AdminBundle\Tests\Fixtures\Admin;

final class PostWithCustomRouteAdmin extends PostAdmin
{
    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return '/post-custom';
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'post_custom';
    }
}
