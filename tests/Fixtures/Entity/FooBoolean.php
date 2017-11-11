<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Entity;

class FooBoolean
{
    private $bar;

    private $baz;

    public function hasBar()
    {
        return $this->bar;
    }

    public function setBar($bar)
    {
        $this->bar = $bar;
    }

    public function isBaz()
    {
        return $this->baz;
    }

    public function setBaz($baz)
    {
        $this->baz = $baz;
    }
}
