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

use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class ModelTypeTest extends TypeTestCase
{
    private ModelType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = new ModelType(PropertyAccess::createPropertyAccessor());
    }

    /**
     * @param array<string, mixed> $options
     *
     * @dataProvider provideGetOptionsCases
     */
    public function testGetOptions(array $options, int $expectedModelManagerFindCalls): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);

        $optionResolver = new OptionsResolver();

        $this->type->configureOptions($optionResolver);

        $resolvedOptions = $optionResolver->resolve(['model_manager' => $modelManager, 'class' => \stdClass::class] + $options);

        static::assertFalse($resolvedOptions['compound']);
        static::assertSame('choice', $resolvedOptions['template']);
        static::assertFalse($resolvedOptions['multiple']);
        static::assertFalse($resolvedOptions['expanded']);
        static::assertInstanceOf(ModelManagerInterface::class, $resolvedOptions['model_manager']);
        static::assertSame(\stdClass::class, $resolvedOptions['class']);
        static::assertNull($resolvedOptions['property']);
        static::assertNull($resolvedOptions['query']);
        static::assertSame($options['choices'] ?? null, $resolvedOptions['choices']);
        static::assertCount(0, $resolvedOptions['preferred_choices']);
        static::assertSame('link_add', $resolvedOptions['btn_add']);
        static::assertSame('link_list', $resolvedOptions['btn_list']);
        static::assertSame('link_delete', $resolvedOptions['btn_delete']);
        static::assertSame('SonataAdminBundle', $resolvedOptions['btn_catalogue']);
        static::assertSame('SonataAdminBundle', $resolvedOptions['btn_translation_domain']);
        static::assertInstanceOf(ModelChoiceLoader::class, $resolvedOptions['choice_loader']);

        $modelManager->expects(static::exactly($expectedModelManagerFindCalls))
            ->method('findBy')
            ->willReturn([]);
        $resolvedOptions['choice_loader']->loadChoiceList();
    }

    /**
     * @phpstan-return iterable<array-key, array{array<string, mixed>, int}>
     */
    public function provideGetOptionsCases(): iterable
    {
        yield [[], 1];
        yield [['choices' => null], 1];
        yield [['choices' => []], 0];
    }

    /**
     * @dataProvider provideCompoundOptionCases
     */
    public function testCompoundOption(bool $expectedCompound, bool $multiple, bool $expanded): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $optionResolver = new OptionsResolver();

        $this->type->configureOptions($optionResolver);

        $options = $optionResolver->resolve(['model_manager' => $modelManager, 'class' => \stdClass::class, 'choices' => [], 'multiple' => $multiple, 'expanded' => $expanded]);

        static::assertSame($expectedCompound, $options['compound']);
        static::assertSame('choice', $options['template']);
        static::assertSame($multiple, $options['multiple']);
        static::assertSame($expanded, $options['expanded']);
        static::assertInstanceOf(ModelManagerInterface::class, $options['model_manager']);
        static::assertSame(\stdClass::class, $options['class']);
        static::assertNull($options['property']);
        static::assertNull($options['query']);
        static::assertCount(0, $options['choices']);
        static::assertCount(0, $options['preferred_choices']);
        static::assertSame('link_add', $options['btn_add']);
        static::assertSame('link_list', $options['btn_list']);
        static::assertSame('link_delete', $options['btn_delete']);
        static::assertSame('SonataAdminBundle', $options['btn_catalogue']);
        static::assertSame('SonataAdminBundle', $options['btn_translation_domain']);
        static::assertInstanceOf(ModelChoiceLoader::class, $options['choice_loader']);
    }

    /**
     * @phpstan-return array<array{bool, bool, bool}>
     */
    public function provideCompoundOptionCases(): iterable
    {
        yield [true, true, true];
        // checkboxes
        yield [false, true, false];
        // select tag (with multiple attribute)
        yield [true, false, true];
        // radio buttons
        yield [false, false, false];
    }
}
