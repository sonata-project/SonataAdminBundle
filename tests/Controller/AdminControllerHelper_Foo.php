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

namespace Sonata\AdminBundle\Tests\Controller;

class AdminControllerHelper_Foo
{
    private $bar;

    public function getAdminTitle()
    {
        return 'foo';
    }

    public function setEnabled($value): void
    {
    }

    public function setBar(AdminControllerHelper_Bar $bar): void
    {
        $this->bar = $bar;
    }

    public function getBar()
    {
        return $this->bar;
    }
}
