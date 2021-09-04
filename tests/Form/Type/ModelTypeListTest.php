<?php

declare(strict_types=1);

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
    public function testGetDefaultOptions(): void
    {
        $type = new ModelTypeList();

        $optionResolver = new OptionsResolver();

        $type->configureOptions($optionResolver);

        $options = $optionResolver->resolve();

        static::assertNull($options['model_manager']);
        static::assertNull($options['class']);
        static::assertSame('link_add', $options['btn_add']);
        static::assertSame('link_edit', $options['btn_edit']);
        static::assertSame('link_list', $options['btn_list']);
        static::assertSame('link_delete', $options['btn_delete']);
        static::assertSame('SonataAdminBundle', $options['btn_catalogue']);
    }
}
