<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\ChoiceList;

use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader;
use Sonata\CoreBundle\Tests\Fixtures\Bundle\Entity\Foo;

class ModelChoiceLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $modelManager = null;

    public function setUp()
    {
        if (false === interface_exists('Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface')) {
            $this->markTestSkipped('Test only available for > SF2.7');
        }

        $this->modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
    }

    public function testLoadFromEntityWithSamePropertyValues()
    {
        $fooA = new Foo();
        $fooA->setBar(1);
        $fooA->setBaz('baz');

        $fooB = new Foo();
        $fooB->setBar(2);
        $fooB->setBaz('baz');

        $this->modelManager->expects($this->once())
            ->method('findBy')
            ->will($this->returnValue(array($fooA, $fooB)));

        $this->modelManager->expects($this->any())
            ->method('getIdentifierValues')
            ->will($this->returnCallback(function (Foo $foo) {
                return array($foo->getBar());
            }));

        $modelChoiceLoader = new ModelChoiceLoader(
            $this->modelManager,
            'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo',
            'baz'
        );

        $expectedChoices = array(
            1 => 'baz (id: 1)',
            2 => 'baz (id: 2)',
        );

        $this->assertSame($expectedChoices, $modelChoiceLoader->loadChoiceList()->getOriginalKeys());
    }
}
