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
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo;

class ModelChoiceLoaderTest extends TestCase
{
    private $modelManager = null;

    public function setUp(): void
    {
        $this->modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
    }

    public function testLoadFromEntityWithSamePropertyValues(): void
    {
        $fooA = new Foo();
        $fooA->setBar(1);
        $fooA->setBaz('baz');

        $fooB = new Foo();
        $fooB->setBar(2);
        $fooB->setBaz('baz');

        $this->modelManager->expects($this->once())
            ->method('findBy')
            ->willReturn([$fooA, $fooB]);

        $this->modelManager
            ->method('getIdentifierValues')
            ->willReturnCallback(static function (Foo $foo) {
                return [$foo->getBar()];
            });

        $modelChoiceLoader = new ModelChoiceLoader(
            $this->modelManager,
            \Sonata\AdminBundle\Tests\Fixtures\Entity\Foo::class,
            'baz'
        );

        $expectedChoices = [
            1 => 'baz (id: 1)',
            2 => 'baz (id: 2)',
        ];

        $this->assertSame($expectedChoices, $modelChoiceLoader->loadChoiceList()->getOriginalKeys());
    }
}
