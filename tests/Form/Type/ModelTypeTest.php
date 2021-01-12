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

        $this->assertFalse($resolvedOptions['compound']);
        $this->assertSame('choice', $resolvedOptions['template']);
        $this->assertFalse($resolvedOptions['multiple']);
        $this->assertFalse($resolvedOptions['expanded']);
        $this->assertInstanceOf(ModelManagerInterface::class, $resolvedOptions['model_manager']);
        $this->assertNull($resolvedOptions['class']);
        $this->assertNull($resolvedOptions['property']);
        $this->assertNull($resolvedOptions['query']);
        $this->assertSame($options['choices'] ?? null, $resolvedOptions['choices']);
        $this->assertCount(0, $resolvedOptions['preferred_choices']);
        $this->assertSame('link_add', $resolvedOptions['btn_add']);
        $this->assertSame('link_list', $resolvedOptions['btn_list']);
        $this->assertSame('link_delete', $resolvedOptions['btn_delete']);
        $this->assertSame('SonataAdminBundle', $resolvedOptions['btn_catalogue']);
        $this->assertInstanceOf(ModelChoiceLoader::class, $resolvedOptions['choice_loader']);

        $modelManager->expects($this->exactly($expectedModelManagerFindCalls))
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

        $this->assertSame($expectedCompound, $options['compound']);
        $this->assertSame('choice', $options['template']);
        $this->assertSame($multiple, $options['multiple']);
        $this->assertSame($expanded, $options['expanded']);
        $this->assertInstanceOf(ModelManagerInterface::class, $options['model_manager']);
        $this->assertNull($options['class']);
        $this->assertNull($options['property']);
        $this->assertNull($options['query']);
        $this->assertCount(0, $options['choices']);
        $this->assertCount(0, $options['preferred_choices']);
        $this->assertSame('link_add', $options['btn_add']);
        $this->assertSame('link_list', $options['btn_list']);
        $this->assertSame('link_delete', $options['btn_delete']);
        $this->assertSame('SonataAdminBundle', $options['btn_catalogue']);
        $this->assertInstanceOf(ModelChoiceLoader::class, $options['choice_loader']);
    }

    public function getCompoundOptionTests()
    {
        return [
            [true, true, true], //checkboxes
            [false, true, false], //select tag (with multiple attribute)
            [true, false, true], //radio buttons
            [false, false, false], //select tag
        ];
    }
}
