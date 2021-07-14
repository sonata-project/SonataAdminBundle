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

namespace Sonata\AdminBundle\Tests\Form\Type\Operator;

use Sonata\AdminBundle\Form\Type\Operator\StringOperatorType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class StringOperatorTypeTest extends TypeTestCase
{
    public function testConfigureOptions(): void
    {
        $formType = new StringOperatorType();
        $optionsResolver = new OptionsResolver();
        $expectedChoices = [
            'label_type_contains' => StringOperatorType::TYPE_CONTAINS,
            'label_type_not_contains' => StringOperatorType::TYPE_NOT_CONTAINS,
            'label_type_equals' => StringOperatorType::TYPE_EQUAL,
            'label_type_starts_with' => StringOperatorType::TYPE_STARTS_WITH,
            'label_type_ends_with' => StringOperatorType::TYPE_ENDS_WITH,
            'label_type_not_equals' => StringOperatorType::TYPE_NOT_EQUAL,
        ];
        $formType->configureOptions($optionsResolver);
        $options = $optionsResolver->resolve([]);
        self::assertSame($expectedChoices, $options['choices']);
        self::assertSame('SonataAdminBundle', $options['choice_translation_domain']);
    }
}
