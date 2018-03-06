<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Entity;

class Foo
{
    private $bar;

    private $baz;

    private $quux;

    public $qux;

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

    protected function getQuux()
    {
        return $this->quux;
    }

    public function setBaz($baz)
    {
        $this->baz = $baz;
    }

    public function setQuux($quux)
    {
        $this->quux = $quux;
    }

    public function __toString()
    {
        return (string) $this->bar;
    }
}
