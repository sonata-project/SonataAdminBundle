<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Entity;

class Foo
{
    private $bar;

    private $baz;

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

    public function __toString()
    {
        return (string) $this->bar;
    }
}
