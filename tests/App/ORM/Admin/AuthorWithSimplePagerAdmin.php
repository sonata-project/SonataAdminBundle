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

namespace SensioLabs\AdminBundle\Tests\App\ORM\Admin;

final class AuthorWithSimplePagerAdmin extends AuthorAdmin
{
    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'author_with_simple_pager';
    }

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'author-with-simple-pager';
    }
}
