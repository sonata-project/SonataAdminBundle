<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Entity;

class FooToString
{
    public function __toString()
    {
        return 'salut';
    }
}
