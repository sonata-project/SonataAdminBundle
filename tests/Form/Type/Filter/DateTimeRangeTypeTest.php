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
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

final class DateTimeRangeTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);

        $type = new DateTimeRangeType($translator);

        $optionsResolver = new OptionsResolver();

        $type->configureOptions($optionsResolver);

        $options = $optionsResolver->resolve();

        $expected = [
            'field_type' => FormDateTimeRangeType::class,
            'field_options' => ['date_format' => DateType::HTML5_FORMAT],
        ];
        $this->assertSame($expected, $options);
    }
}
