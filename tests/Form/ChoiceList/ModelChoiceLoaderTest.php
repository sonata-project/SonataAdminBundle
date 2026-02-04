<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Form\ChoiceList;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Form\ChoiceList\ModelChoiceLoader;
use SensioLabs\AdminBundle\Model\ModelManagerInterface;
use SensioLabs\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class ModelChoiceLoaderTest extends TestCase
{
    /**
     * @var MockObject&ModelManagerInterface<object>
     */
    private ModelManagerInterface $modelManager;

    private PropertyAccessorInterface $propertyAccessor;

    protected function setUp(): void
    {
        $this->modelManager = $this->createMock(ModelManagerInterface::class);
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function testConstructWithUnsupportedQuery(): void
    {
        $this->modelManager->method('supportsQuery')->willReturn(false);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The model manager does not support the query.');

        new ModelChoiceLoader($this->modelManager, $this->propertyAccessor, \stdClass::class, null, new \stdClass());
    }

    public function testLoadFromEntityWithSamePropertyValues(): void
    {
        $fooA = new Foo();
        $fooA->setBar('1');
        $fooA->setBaz('baz');

        $fooB = new Foo();
        $fooB->setBar('2');
        $fooB->setBaz('baz');

        $this->modelManager->expects(static::once())
            ->method('findBy')
            ->willReturn([$fooA, $fooB]);

        $this->modelManager
            ->method('getNormalizedIdentifier')
            ->willReturnCallback(static fn (Foo $foo): ?string => $foo->getBar());

        $modelChoiceLoader = new ModelChoiceLoader(
            $this->modelManager,
            $this->propertyAccessor,
            \SensioLabs\AdminBundle\Tests\Fixtures\Entity\Foo::class,
            'baz'
        );

        $expectedChoices = [
            1 => 'baz (id: 1)',
            2 => 'baz (id: 2)',
        ];

        static::assertSame($expectedChoices, $modelChoiceLoader->loadChoiceList()->getOriginalKeys());
    }
}
