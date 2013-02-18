<?php

namespace Sonata\AdminBundle\Tests\Form\Type;

use Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType;
use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

class DateTimeRangeTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions()
    {
        $stub = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        $formType = new DateTimeRangeType($stub);

        $this->assertTrue(is_array($formType->getDefaultOptions(array())));
    }
}
