<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Entity;

class FooCall
{
    public function __call($method, $arguments)
    {
        return array($method, $arguments);
    }
}
