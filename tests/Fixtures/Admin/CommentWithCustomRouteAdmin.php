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

final class CommentWithCustomRouteAdmin extends CommentAdmin
{
    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'comment-custom';
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'comment_custom';
    }
}
