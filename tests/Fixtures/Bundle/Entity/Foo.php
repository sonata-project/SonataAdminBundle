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

namespace Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity;

class Foo
{
    private $bar;

    private $baz;

    public function getBar()
    {
        return $this->bar;
    }

    public function setBar($bar): void
    {
        $this->bar = $bar;
    }

    public function getBaz()
    {
        return $this->baz;
    }

    public function setBaz($baz): void
    {
        $this->baz = $baz;
    }
}
