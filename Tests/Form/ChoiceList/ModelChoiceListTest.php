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

use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo;

class ModelChoiceListTest extends \PHPUnit_Framework_TestCase
{
    private $modelManager = null;

    public function setUp()
    {
        if (false === interface_exists('Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList')) {
            $this->markTestSkipped('Test only available for < SF3.0');
        }

        $this->modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        $this->modelManager->expects($this->any())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(array('foo', 'bar')));
    }

    public function testLoadFromEntity()
    {
        // Get choices From Entity, count($this->identifier) > 1
        $fooA = new Foo();
        $fooA->setBar(1);
        $fooB = new Foo();
        $fooB->setBar(2);

        $result = array($fooA, $fooB);

        $this->modelManager->expects($this->once())
            ->method('findBy')
            ->will($this->returnValue($result));

        $modelChoice = new ModelChoiceList(
            $this->modelManager,
            'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo',
            'bar'
        );

        $this->assertSame(array_keys($result), $modelChoice->getChoices());
    }

    public function testLoadFromCustomQuery()
    {
        // Get choices From Custom Query, count($this->identifier) > 1
        $result = array(1, 2);

        $this->modelManager->expects($this->any())
            ->method('executeQuery')
            ->will($this->returnValue($result));

        $modelChoice = new ModelChoiceList(
            $this->modelManager,
            'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo',
            null,
            'SELECT foo, baz from foo'
        );

        $this->assertSame(array_keys($result), $modelChoice->getChoices());
    }

    public function testLoadArrayOfChoices()
    {
        // Get choices from Array of choices, count($this->identifier) > 1
        $result = array(1, 2);
        $modelChoice = new ModelChoiceList(
            $this->modelManager,
            'Sonata\AdminBundle\Tests\Fixtures\Entity\Foo',
            null,
            null,
            $result
        );

        $this->assertSame(array_keys($result), $modelChoice->getChoices());
    }
}
