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

use Sonata\AdminBundle\Form\Type\TranslatableChoiceType;
use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslatableChoiceTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions()
    {
        $stub = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        $type = new TranslatableChoiceType($stub);

        $optionResolver = new OptionsResolver();

        $this->assertEquals('choice', $type->getParent());

        $type->setDefaultOptions($optionResolver);

        $options = $optionResolver->resolve(array('catalogue' => 'foo'));

        $this->assertEquals('foo', $options['catalogue']);
    }
}
