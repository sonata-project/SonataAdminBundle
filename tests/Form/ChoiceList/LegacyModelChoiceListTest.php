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

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo;
use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;

class LegacyModelChoiceListTest extends TestCase
{
    private $modelManager;

    protected function setUp(): void
    {
        if (!class_exists(SimpleChoiceList::class)) {
            $this->markTestSkipped('Test only available for <= SF2.8');
        }

        $this->modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);

        $this->modelManager
            ->method('getIdentifierFieldNames')
            ->willReturn(['foo', 'bar']);
    }

    public function testLoadFromEntity(): void
    {
        // Get choices From Entity, count($this->identifier) > 1
        $fooA = new Foo();
        $fooA->setBar(1);
        $fooB = new Foo();
        $fooB->setBar(2);

        $result = [$fooA, $fooB];

        $this->modelManager->expects($this->once())
            ->method('findBy')
            ->willReturn($result);

        $modelChoice = new ModelChoiceList(
            $this->modelManager,
            \Sonata\AdminBundle\Tests\Fixtures\Entity\Foo::class,
            'bar'
        );

        $this->assertSame(array_keys($result), $modelChoice->getChoices());
    }

    public function testLoadFromCustomQuery(): void
    {
        // Get choices From Custom Query, count($this->identifier) > 1
        $result = [1, 2];

        $this->modelManager
            ->method('executeQuery')
            ->willReturn($result);

        $modelChoice = new ModelChoiceList(
            $this->modelManager,
            \Sonata\AdminBundle\Tests\Fixtures\Entity\Foo::class,
            null,
            'SELECT foo, baz from foo'
        );

        $this->assertSame(array_keys($result), $modelChoice->getChoices());
    }

    public function testLoadArrayOfChoices(): void
    {
        // Get choices from Array of choices, count($this->identifier) > 1
        $result = [1, 2];
        $modelChoice = new ModelChoiceList(
            $this->modelManager,
            \Sonata\AdminBundle\Tests\Fixtures\Entity\Foo::class,
            null,
            null,
            $result
        );

        $this->assertSame(array_keys($result), $modelChoice->getChoices());
    }
}
