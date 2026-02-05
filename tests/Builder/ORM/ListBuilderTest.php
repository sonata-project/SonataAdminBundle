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

namespace SensioLabs\AdminBundle\Tests\Builder\ORM;

use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Datagrid\ListMapper;
use SensioLabs\AdminBundle\FieldDescription\TypeGuesserInterface;
use SensioLabs\AdminBundle\Builder\ORM\ListBuilder;
use SensioLabs\AdminBundle\FieldDescription\ORM\FieldDescription;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * @author Andrew Mor-Yaroslavtsev <andrejs@gmail.com>
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ListBuilderTest extends TestCase
{
    /**
     * @var Stub&TypeGuesserInterface
     */
    private TypeGuesserInterface $typeGuesser;

    private ListBuilder $listBuilder;

    /**
     * @var MockObject&AdminInterface<object>
     */
    private AdminInterface $admin;

    protected function setUp(): void
    {
        $this->typeGuesser = static::createStub(TypeGuesserInterface::class);
        $this->admin = $this->createMock(AdminInterface::class);

        $this->admin->method('getClass')->willReturn('Foo');

        $this->listBuilder = new ListBuilder($this->typeGuesser);
    }

    public function testAddListActionField(): void
    {
        $this->admin->expects(static::once())->method('addListFieldDescription');

        $fieldDescription = new FieldDescription('foo');
        $fieldDescription->setAdmin($this->admin);

        $list = $this->listBuilder->getBaseList();
        $this->listBuilder->addField($list, 'actions', $fieldDescription);

        static::assertSame(
            '@SonataAdmin/CRUD/list__action.html.twig',
            $list->get('foo')->getTemplate(),
            'Custom list action field has a default list action template assigned'
        );
    }

    public function testCorrectFixedActionsFieldType(): void
    {
        $this->admin->expects(static::once())->method('addListFieldDescription');
        $this->typeGuesser->method('guess')->willReturn(new TypeGuess(ListMapper::TYPE_ACTIONS, [], Guess::LOW_CONFIDENCE));

        $fieldDescription = new FieldDescription('_action');
        $fieldDescription->setOption('actions', ['test' => []]);
        $fieldDescription->setAdmin($this->admin);

        $list = $this->listBuilder->getBaseList();
        $this->listBuilder->addField($list, null, $fieldDescription);

        static::assertSame(
            ListMapper::TYPE_ACTIONS,
            $list->get('_action')->getType(),
            'Standard list _action field has "actions" type'
        );

        $actions = $fieldDescription->getOption('actions');
        static::assertIsArray($actions);
        static::assertArrayHasKey('test', $actions);
        static::assertIsArray($actions['test']);
        static::assertArrayHasKey('template', $actions['test']);
        static::assertSame(
            '@SonataAdmin/CRUD/list__action_test.html.twig',
            $fieldDescription->getOption('actions')['test']['template']
        );
    }

    #[DataProvider('provideFixFieldDescriptionCases')]
    public function testFixFieldDescription(int $type, string $template): void
    {
        $this->admin->expects(static::once())->method('attachAdminClass');

        $fieldDescription = new FieldDescription('test', [], ['type' => $type]);
        $fieldDescription->setOption('sortable', true);
        $fieldDescription->setType('fakeType');
        $fieldDescription->setAdmin($this->admin);

        $this->listBuilder->fixFieldDescription($fieldDescription);

        static::assertSame($template, $fieldDescription->getTemplate());
    }

    /**
     * @phpstan-return iterable<array-key, array{int, string}>
     */
    public static function provideFixFieldDescriptionCases(): iterable
    {
        yield 'one-to-one' => [
            ClassMetadata::ONE_TO_ONE,
            '@SonataAdmin/CRUD/Association/list_one_to_one.html.twig',
        ];
        yield 'many-to-one' => [
            ClassMetadata::MANY_TO_ONE,
            '@SonataAdmin/CRUD/Association/list_many_to_one.html.twig',
        ];
        yield 'one-to-many' => [
            ClassMetadata::ONE_TO_MANY,
            '@SonataAdmin/CRUD/Association/list_one_to_many.html.twig',
        ];
        yield 'many-to-many' => [
            ClassMetadata::MANY_TO_MANY,
            '@SonataAdmin/CRUD/Association/list_many_to_many.html.twig',
        ];
    }

    public function testFixFieldDescriptionException(): void
    {
        $this->expectException(\RuntimeException::class);

        $fieldDescription = new FieldDescription('name');
        $fieldDescription->setAdmin($this->admin);
        $this->listBuilder->fixFieldDescription($fieldDescription);
    }
}
