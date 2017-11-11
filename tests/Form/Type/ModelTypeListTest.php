<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\Type;

use Sonata\AdminBundle\Form\Type\ModelTypeList;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @group legacy
 *
 * NEXT_MAJOR: Change test class name and content according to the renaming.
 */
class ModelTypeListTest extends TypeTestCase
{
    public function testGetDefaultOptions()
    {
        $type = new ModelTypeList();

        $optionResolver = new OptionsResolver();

        if (!method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $type->setDefaultOptions($optionResolver);
        } else {
            $type->configureOptions($optionResolver);
        }

        $options = $optionResolver->resolve();

        $this->assertNull($options['model_manager']);
        $this->assertNull($options['class']);
        $this->assertSame('link_add', $options['btn_add']);
        $this->assertSame('link_list', $options['btn_list']);
        $this->assertSame('link_delete', $options['btn_delete']);
        $this->assertSame('SonataAdminBundle', $options['btn_catalogue']);
    }
}
