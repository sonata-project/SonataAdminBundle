<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Fixtures\Entity;

class Foo
{
    public $qux;
    private $bar;

    private $baz;

    private $quux;

    public function __toString()
    {
        return (string) $this->bar;
    }

    public function getBar()
    {
        return $this->bar;
    }

    public function setBar($bar)
    {
        $this->bar = $bar;
    }

    public function getBaz()
    {
        return $this->baz;
    }

    public function setBaz($baz)
    {
        $this->baz = $baz;
    }

    public function setQuux($quux)
    {
        $this->quux = $quux;
    }

    protected function getQuux()
    {
        return $this->quux;
    }
}
