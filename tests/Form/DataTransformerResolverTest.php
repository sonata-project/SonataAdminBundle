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

namespace Sonata\AdminBundle\Tests\Form;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Form\DataTransformerResolver;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Entity\AbstractEntity;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Entity;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @author Peter Gribanov <info@peter-gribanov.ru>
 */
final class DataTransformerResolverTest extends TestCase
{
    private DataTransformerResolver $resolver;

    /**
     * @var FieldDescriptionInterface&MockObject
     */
    private FieldDescriptionInterface $fieldDescription;

    /**
     * @var ModelManagerInterface<object>&MockObject
     */
    private ModelManagerInterface $modelManager;

    protected function setUp(): void
    {
        $this->fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $this->modelManager = $this->createMock(ModelManagerInterface::class);
        $this->resolver = new DataTransformerResolver();
    }

    public function testFailedResolve(): void
    {
        static::assertNull($this->resolve());
    }

    /**
     * @phpstan-return iterable<array{string}>
     */
    public function provideFieldTypes(): iterable
    {
        yield ['foo'];
        // override predefined transformers
        yield ['date'];
        yield ['boolean'];
        yield ['choice'];
    }

    /**
     * @dataProvider provideFieldTypes
     */
    public function testResolveCustomDataTransformer(string $fieldType): void
    {
        $customDataTransformer = new CallbackTransformer(
            static fn (mixed $value): string => (string) (int) $value,
            static fn (mixed $value): bool => filter_var($value, \FILTER_VALIDATE_BOOLEAN)
        );
        $this->fieldDescription->method('getOption')->with('data_transformer')->willReturn($customDataTransformer);
        $this->fieldDescription->method('getType')->willReturn($fieldType);

        $dataTransformer = $this->resolve();

        static::assertInstanceOf(DataTransformerInterface::class, $dataTransformer);
        static::assertSame($customDataTransformer, $dataTransformer);
    }

    /**
     * @phpstan-return iterable<array-key, array{mixed, \DateTimeZone}>
     */
    public function provideResolveDateDataTransformerCases(): iterable
    {
        $default = new \DateTimeZone(date_default_timezone_get());
        $custom = new \DateTimeZone('Europe/Rome');
        yield 'empty timezone' => [null, $default];
        yield 'disabled timezone' => [false, $default];
        yield 'default timezone by name' => [$default->getName(), $default];
        yield 'default timezone by object' => [$default, $default];
        yield 'custom timezone by name' => [$custom->getName(), $custom];
        yield 'custom timezone by object' => [$custom, $custom];
    }

    /**
     * @dataProvider provideResolveDateDataTransformerCases
     */
    public function testResolveDateDataTransformer(mixed $timezone, \DateTimeZone $expectedTimezone): void
    {
        $this->fieldDescription->method('getOption')->willReturnMap([
            ['data_transformer', null, null],
            ['timezone', null, $timezone],
        ]);
        $this->fieldDescription->method('getType')->willReturn('date');

        $dataTransformer = $this->resolve();

        static::assertInstanceOf(DateTimeToStringTransformer::class, $dataTransformer);

        $value = '2020-12-12';
        $defaultTimezone = new \DateTimeZone(date_default_timezone_get());
        $expectedDate = new \DateTime($value, $expectedTimezone);
        $expectedDate->setTimezone($defaultTimezone);

        $resultDate = $dataTransformer->reverseTransform($value);

        static::assertInstanceOf(\DateTime::class, $resultDate);
        static::assertSame($expectedDate->format(\DateTime::ATOM), $resultDate->format(\DateTime::ATOM));
        static::assertSame($defaultTimezone->getName(), $resultDate->getTimezone()->getName());

        // test laze-load
        $secondDataTransformer = $this->resolve();

        static::assertSame($dataTransformer, $secondDataTransformer);
    }

    public function testResolveChoiceWithoutClassName(): void
    {
        $this->fieldDescription->method('getType')->willReturn('choice');

        static::assertNull($this->resolve());
    }

    public function testResolveChoiceBadClassName(): void
    {
        $this->fieldDescription->method('getOption')->willReturnMap([
            ['data_transformer', null, null],
            ['class', null, \stdClass::class],
        ]);
        $this->fieldDescription->method('getType')->willReturn('choice');
        $this->fieldDescription->method('getTargetModel')->willReturn(\DateTime::class);

        static::assertNull($this->resolve());
    }

    public function testResolveChoice(): void
    {
        $newId = 1;
        $className = Entity::class;
        $object = new Entity(1);

        $this->fieldDescription->method('getOption')->willReturnMap([
            ['data_transformer', null, null],
            ['class', null, $className],
        ]);
        $this->fieldDescription->method('getType')->willReturn('choice');
        $this->fieldDescription->method('getTargetModel')->willReturn($className);
        $this->modelManager->method('find')->with($className, $newId)->willReturn($object);

        $dataTransformer = $this->resolve();

        static::assertInstanceOf(ModelToIdTransformer::class, $dataTransformer);
        static::assertSame($object, $dataTransformer->reverseTransform($newId));
    }

    public function testResolveChoiceWithAbstractClass(): void
    {
        $newId = 1;
        $targetModel = Entity::class;
        $className = AbstractEntity::class;
        $object = new Entity(2);

        $this->fieldDescription->method('getOption')->willReturnMap([
            ['data_transformer', null, null],
            ['class', null, $className],
        ]);
        $this->fieldDescription->method('getType')->willReturn('choice');
        $this->fieldDescription->method('getTargetModel')->willReturn($targetModel);
        $this->modelManager->method('find')->with($className, $newId)->willReturn($object);

        $dataTransformer = $this->resolve();

        static::assertInstanceOf(ModelToIdTransformer::class, $dataTransformer);
        static::assertSame($object, $dataTransformer->reverseTransform($newId));
    }

    /**
     * @dataProvider provideFieldTypes
     */
    public function testCustomGlobalTransformers(string $fieldType): void
    {
        $customDataTransformer = new CallbackTransformer(
            static fn (mixed $value): string => (string) (int) $value,
            static fn (mixed $value): bool => filter_var($value, \FILTER_VALIDATE_BOOLEAN)
        );

        $this->fieldDescription->method('getType')->willReturn($fieldType);

        $this->resolver = new DataTransformerResolver([
            $fieldType => $customDataTransformer, // override predefined transformer
        ]);

        $dataTransformer = $this->resolve();

        static::assertInstanceOf(DataTransformerInterface::class, $dataTransformer);
        static::assertSame($customDataTransformer, $dataTransformer);
    }

    /**
     * @dataProvider provideFieldTypes
     */
    public function testAddCustomGlobalTransformer(string $fieldType): void
    {
        $customDataTransformer = new CallbackTransformer(
            static fn (mixed $value): string => (string) (int) $value,
            static fn (mixed $value): bool => filter_var($value, \FILTER_VALIDATE_BOOLEAN)
        );

        $this->fieldDescription->method('getType')->willReturn($fieldType);

        $this->resolver->addCustomGlobalTransformer($fieldType, $customDataTransformer);

        $dataTransformer = $this->resolve();

        static::assertInstanceOf(DataTransformerInterface::class, $dataTransformer);
        static::assertSame($customDataTransformer, $dataTransformer);
    }

    /**
     * @return DataTransformerInterface<mixed, mixed>|null
     */
    private function resolve(): ?DataTransformerInterface
    {
        return $this->resolver->resolve($this->fieldDescription, $this->modelManager);
    }
}
