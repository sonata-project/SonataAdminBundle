<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Entity;

class FooToStringNull
{
    // In case __toString returns an attribute not yet set
    public function __toString()
    {
        return null;
    }
}
