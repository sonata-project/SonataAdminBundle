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

use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollectionTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions()
    {
        $type = new CollectionType();

        $optionResolver = new OptionsResolver();

        $type->setDefaultOptions($optionResolver);

        $options = $optionResolver->resolve();

        $this->assertFalse($options['modifiable']);
        $this->assertEquals('text', $options['type']);
        $this->assertEquals(0, count($options['type_options']));
        $this->assertEquals('link_add', $options['btn_add']);
        $this->assertEquals('SonataAdminBundle', $options['btn_catalogue']);
    }
}
