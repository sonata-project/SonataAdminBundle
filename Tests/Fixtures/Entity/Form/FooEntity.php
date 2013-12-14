<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Entity\Form;

class FooEntity
{
    private $values;

    public function __construct(array $values=array())
    {
        $this->values = $values;
    }

}
