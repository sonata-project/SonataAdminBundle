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
    /**
     * @var ModelType
     */
    protected $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = new ModelType(PropertyAccess::createPropertyAccessor());
    }

    /**
     * @param array<string, mixed> $options
     *
     * @dataProvider getGetOptionsTests
     */
    public function testGetOptions(array $options, int $expectedModelManagerFindCalls): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);

        $optionResolver = new OptionsResolver();

        $this->type->configureOptions($optionResolver);

        $resolvedOptions = $optionResolver->resolve(['model_manager' => $modelManager, 'class' => \stdClass::class] + $options);

        self::assertFalse($resolvedOptions['compound']);
        self::assertSame('choice', $resolvedOptions['template']);
        self::assertFalse($resolvedOptions['multiple']);
        self::assertFalse($resolvedOptions['expanded']);
        self::assertInstanceOf(ModelManagerInterface::class, $resolvedOptions['model_manager']);
        self::assertSame(\stdClass::class, $resolvedOptions['class']);
        self::assertNull($resolvedOptions['property']);
        self::assertNull($resolvedOptions['query']);
        self::assertSame($options['choices'] ?? null, $resolvedOptions['choices']);
        self::assertCount(0, $resolvedOptions['preferred_choices']);
        self::assertSame('link_add', $resolvedOptions['btn_add']);
        self::assertSame('link_list', $resolvedOptions['btn_list']);
        self::assertSame('link_delete', $resolvedOptions['btn_delete']);
        self::assertSame('SonataAdminBundle', $resolvedOptions['btn_catalogue']);
        self::assertInstanceOf(ModelChoiceLoader::class, $resolvedOptions['choice_loader']);

        $modelManager->expects(self::exactly($expectedModelManagerFindCalls))
            ->method('findBy')
            ->willReturn([]);
        $resolvedOptions['choice_loader']->loadChoiceList();
    }

    /**
     * @phpstan-return iterable<array-key, array{array<string, mixed>, int}>
     */
    public function getGetOptionsTests(): iterable
    {
        return [
            [[], 1],
            [['choices' => null], 1],
            [['choices' => []], 0],
        ];
    }

    /**
     * @dataProvider getCompoundOptionTests
     */
    public function testCompoundOption(bool $expectedCompound, bool $multiple, bool $expanded): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $optionResolver = new OptionsResolver();

        $this->type->configureOptions($optionResolver);

        $options = $optionResolver->resolve(['model_manager' => $modelManager, 'class' => \stdClass::class, 'choices' => [], 'multiple' => $multiple, 'expanded' => $expanded]);

        self::assertSame($expectedCompound, $options['compound']);
        self::assertSame('choice', $options['template']);
        self::assertSame($multiple, $options['multiple']);
        self::assertSame($expanded, $options['expanded']);
        self::assertInstanceOf(ModelManagerInterface::class, $options['model_manager']);
        self::assertSame(\stdClass::class, $options['class']);
        self::assertNull($options['property']);
        self::assertNull($options['query']);
        self::assertCount(0, $options['choices']);
        self::assertCount(0, $options['preferred_choices']);
        self::assertSame('link_add', $options['btn_add']);
        self::assertSame('link_list', $options['btn_list']);
        self::assertSame('link_delete', $options['btn_delete']);
        self::assertSame('SonataAdminBundle', $options['btn_catalogue']);
        self::assertInstanceOf(ModelChoiceLoader::class, $options['choice_loader']);
    }

    /**
     * @phpstan-return array<array{bool, bool, bool}>
     */
    public function getCompoundOptionTests(): array
    {
        return [
            [true, true, true], //checkboxes
            [false, true, false], //select tag (with multiple attribute)
            [true, false, true], //radio buttons
            [false, false, false], //select tag
        ];
    }
}
