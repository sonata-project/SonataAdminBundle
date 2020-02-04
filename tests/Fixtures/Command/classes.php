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

namespace Sonata\AdminBundle\Tests\Fixtures\Controller
{
abstract class AbstractFooAdminController
{
    public function bazAction(): void
    {
    }
}
}

namespace Sonata\AdminBundle\Tests\Fixtures\Controller
{

class FooAdminController extends AbstractFooAdminController
{
    public function fooAction($baz): void
    {
    }
}
}

namespace Sonata\AdminBundle\Tests\Fixtures\Controller
{
class BarAdminController
{
    public function barAction(): void
    {
    }
}
}
