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

namespace Sonata\AdminBundle\Tests\Form\Type\Filter;

use Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType;
use Sonata\Form\Type\DateTimeRangeType as FormDateTimeRangeType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class DateTimeRangeTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions(): void
    {
        $stub = $this->createStub(TranslatorInterface::class);

        $type = new DateTimeRangeType($stub);

        $optionResolver = new OptionsResolver();

        $type->configureOptions($optionResolver);

        $options = $optionResolver->resolve();

        $expected = [
            'field_type' => FormDateTimeRangeType::class,
            'field_options' => ['date_format' => 'yyyy-MM-dd'],
        ];
        $this->assertSame($expected, $options);
    }
}
