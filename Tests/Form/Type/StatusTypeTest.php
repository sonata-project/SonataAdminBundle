<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\Type;

use Sonata\AdminBundle\Form\Type\StatusType;

use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Choice
{
    public static function getList()
    {
        return array(
            1 => 'salut'
        );
    }
}

class StatusTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions()
    {
        $type = new StatusType('Sonata\AdminBundle\Tests\Form\Type\Choice', 'getList', 'choice_type');

        $this->assertEquals('choice_type', $type->getName());
        $this->assertEquals('choice', $type->getParent());

        $optionResolver = new OptionsResolver();

        $type->setDefaultOptions($optionResolver);

        $options = $optionResolver->resolve(array());

        $this->assertArrayHasKey('choices', $options);
        $this->assertEquals($options['choices'], array(1 => 'salut'));
    }
}
