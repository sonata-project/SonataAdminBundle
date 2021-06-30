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

namespace Sonata\AdminBundle\Tests\Form\ChoiceList;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ModelChoiceLoaderTest extends TestCase
{
    /**
     * @var MockObject&ModelManagerInterface <object>
     */
    private $modelManager;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

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
        $fooA->setBar(1);
        $fooA->setBaz('baz');

        $fooB = new Foo();
        $fooB->setBar(2);
        $fooB->setBaz('baz');

        $this->modelManager->expects(self::once())
            ->method('findBy')
            ->willReturn([$fooA, $fooB]);

        $this->modelManager
            ->method('getIdentifierValues')
            ->willReturnCallback(static function (Foo $foo): array {
                return [$foo->getBar()];
            });

        $modelChoiceLoader = new ModelChoiceLoader(
            $this->modelManager,
            $this->propertyAccessor,
            \Sonata\AdminBundle\Tests\Fixtures\Entity\Foo::class,
            'baz'
        );

        $expectedChoices = [
            1 => 'baz (id: 1)',
            2 => 'baz (id: 2)',
        ];

        self::assertSame($expectedChoices, $modelChoiceLoader->loadChoiceList()->getOriginalKeys());
    }
}
