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

class ModelTypeTest extends TypeTestCase
{
    protected $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = new ModelType(PropertyAccess::createPropertyAccessor());
    }

    /**
     * @dataProvider getGetOptionsTests
     */
    public function testGetOptions(array $options, int $expectedModelManagerFindCalls): void
    {
        $modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);

        $optionsResolver = new OptionsResolver();

        $this->type->configureOptions($optionsResolver);

        $resolvedOptions = $optionsResolver->resolve(['model_manager' => $modelManager] + $options);

        static::assertFalse($resolvedOptions['compound']);
        static::assertSame('choice', $resolvedOptions['template']);
        static::assertFalse($resolvedOptions['multiple']);
        static::assertFalse($resolvedOptions['expanded']);
        static::assertInstanceOf(ModelManagerInterface::class, $resolvedOptions['model_manager']);
        static::assertNull($resolvedOptions['class']);
        static::assertNull($resolvedOptions['property']);
        static::assertNull($resolvedOptions['query']);
        static::assertSame($options['choices'] ?? null, $resolvedOptions['choices']);
        static::assertCount(0, $resolvedOptions['preferred_choices']);
        static::assertSame('link_add', $resolvedOptions['btn_add']);
        static::assertSame('link_list', $resolvedOptions['btn_list']);
        static::assertSame('link_delete', $resolvedOptions['btn_delete']);
        static::assertSame('SonataAdminBundle', $resolvedOptions['btn_catalogue']);
        static::assertInstanceOf(ModelChoiceLoader::class, $resolvedOptions['choice_loader']);

        $modelManager->expects(static::exactly($expectedModelManagerFindCalls))
            ->method('findBy')
            ->willReturn([]);
        $resolvedOptions['choice_loader']->loadChoiceList();
    }

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
        $modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
        $optionResolver = new OptionsResolver();

        $this->type->configureOptions($optionResolver);

        $options = $optionResolver->resolve(['model_manager' => $modelManager, 'choices' => [], 'multiple' => $multiple, 'expanded' => $expanded]);

        static::assertSame($expectedCompound, $options['compound']);
        static::assertSame('choice', $options['template']);
        static::assertSame($multiple, $options['multiple']);
        static::assertSame($expanded, $options['expanded']);
        static::assertInstanceOf(ModelManagerInterface::class, $options['model_manager']);
        static::assertNull($options['class']);
        static::assertNull($options['property']);
        static::assertNull($options['query']);
        static::assertCount(0, $options['choices']);
        static::assertCount(0, $options['preferred_choices']);
        static::assertSame('link_add', $options['btn_add']);
        static::assertSame('link_list', $options['btn_list']);
        static::assertSame('link_delete', $options['btn_delete']);
        static::assertSame('SonataAdminBundle', $options['btn_catalogue']);
        static::assertInstanceOf(ModelChoiceLoader::class, $options['choice_loader']);
    }

    public function getCompoundOptionTests()
    {
        return [
            [true, true, true], // checkboxes
            [false, true, false], // select tag (with multiple attribute)
            [true, false, true], // radio buttons
            [false, false, false], // select tag
        ];
    }
}
