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

use Sonata\AdminBundle\Form\Type\AdminType;
use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions()
    {
        $type = new AdminType();

        $optionResolver = new OptionsResolver();

        $type->setDefaultOptions($optionResolver);

        $options = $optionResolver->resolve();

        $this->assertTrue($options['delete']);
        $this->assertFalse($options['auto_initialize']);
        $this->assertEquals('link_add', $options['btn_add']);
        $this->assertEquals('link_list', $options['btn_list']);
        $this->assertEquals('link_delete', $options['btn_delete']);
        $this->assertEquals('SonataAdminBundle', $options['btn_catalogue']);
    }
}
