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
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionCollection;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;
use SensioLabs\AdminBundle\FieldDescription\TypeGuesserInterface;
use SensioLabs\AdminBundle\Builder\ORM\ShowBuilder;
use SensioLabs\AdminBundle\FieldDescription\ORM\FieldDescription;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ShowBuilderTest extends TestCase
{
    /**
     * @var Stub&TypeGuesserInterface
     */
    private TypeGuesserInterface $guesser;

    private ShowBuilder $showBuilder;

    /**
     * @var MockObject&AdminInterface<object>
     */
    private AdminInterface $admin;

    protected function setUp(): void
    {
        $this->guesser = static::createStub(TypeGuesserInterface::class);

        $this->showBuilder = new ShowBuilder(
            $this->guesser,
            [
                'fakeTemplate' => 'fake',
                FieldDescriptionInterface::TYPE_ONE_TO_ONE => '@SonataAdmin/CRUD/Association/show_one_to_one.html.twig',
                FieldDescriptionInterface::TYPE_ONE_TO_MANY => '@SonataAdmin/CRUD/Association/show_one_to_many.html.twig',
                FieldDescriptionInterface::TYPE_MANY_TO_ONE => '@SonataAdmin/CRUD/Association/show_many_to_one.html.twig',
                FieldDescriptionInterface::TYPE_MANY_TO_MANY => '@SonataAdmin/CRUD/Association/show_many_to_many.html.twig',
            ]
        );

        $this->admin = $this->createMock(AdminInterface::class);

        $this->admin->method('getClass')->willReturn('FakeClass');
    }

    public function testGetBaseList(): void
    {
        static::assertInstanceOf(FieldDescriptionCollection::class, $this->showBuilder->getBaseList());
    }

    public function testAddFieldNoType(): void
    {
        $typeGuess = static::createStub(TypeGuess::class);

        $fieldDescription = new FieldDescription('FakeName', [], ['type' => ClassMetadata::MANY_TO_ONE]);
        $fieldDescription->setAdmin($this->admin);

        $this->admin->expects(static::once())->method('attachAdminClass');
        $this->admin->expects(static::once())->method('addShowFieldDescription');

        $typeGuess->method('getType')->willReturn('fakeType');

        $this->guesser->method('guess')->willReturn($typeGuess);

        $this->showBuilder->addField(
            new FieldDescriptionCollection(),
            null,
            $fieldDescription
        );
    }

    public function testAddFieldWithType(): void
    {
        $fieldDescription = new FieldDescription('FakeName');
        $fieldDescription->setAdmin($this->admin);

        $this->admin->expects(static::once())->method('addShowFieldDescription');

        $this->showBuilder->addField(
            new FieldDescriptionCollection(),
            'someType',
            $fieldDescription
        );
    }

    #[DataProvider('provideFixFieldDescriptionCases')]
    public function testFixFieldDescription(string $type, int $mappingType, string $template): void
    {
        $fieldDescription = new FieldDescription('FakeName', [], ['type' => $mappingType]);
        $fieldDescription->setType($type);
        $fieldDescription->setAdmin($this->admin);

        $this->admin->expects(static::once())->method('attachAdminClass');

        $this->showBuilder->fixFieldDescription($fieldDescription);

        static::assertSame($template, $fieldDescription->getTemplate());
    }

    /**
     * @phpstan-return iterable<array-key, array{string, int, string}>
     */
    public static function provideFixFieldDescriptionCases(): iterable
    {
        yield 'one-to-one' => [
            FieldDescriptionInterface::TYPE_ONE_TO_ONE,
            ClassMetadata::ONE_TO_ONE,
            '@SonataAdmin/CRUD/Association/show_one_to_one.html.twig',
        ];
        yield 'many-to-one' => [
            FieldDescriptionInterface::TYPE_MANY_TO_ONE,
            ClassMetadata::MANY_TO_ONE,
            '@SonataAdmin/CRUD/Association/show_many_to_one.html.twig',
        ];
        yield 'one-to-many' => [
            FieldDescriptionInterface::TYPE_ONE_TO_MANY,
            ClassMetadata::ONE_TO_MANY,
            '@SonataAdmin/CRUD/Association/show_one_to_many.html.twig',
        ];
        yield 'many-to-many' => [
            FieldDescriptionInterface::TYPE_MANY_TO_MANY,
            ClassMetadata::MANY_TO_MANY,
            '@SonataAdmin/CRUD/Association/show_many_to_many.html.twig',
        ];
    }

    public function testFixFieldDescriptionException(): void
    {
        $this->expectException(\RuntimeException::class);

        $fieldDescription = new FieldDescription('name');
        $fieldDescription->setAdmin($this->admin);
        $this->showBuilder->fixFieldDescription($fieldDescription);
    }
}
