<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Miłosz Chmura
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\EventListener;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\EventListener\MergeCollectionListener;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormEvent;

class MergeCollectionListenerTest extends \PHPUnit_Framework_TestCase
{
    private $dispatcher;

    private $factory;
    
    private $modelManager;

    private $builder;

    protected function setUp()
    {
        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->factory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $this->modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        
        $this->modelManager
            ->expects($this->any())
            ->method('collectionRemoveElement')
            ->will($this->returnCallback(function(&$collection, &$element) {
                $key = array_search($element, $collection, true);

                if ($key === false) {
                    return false;
                }

                unset($collection[$key]);
                return true;
            }));
        $this->modelManager
            ->expects($this->any())
            ->method('collectionAddElement')
            ->will($this->returnCallback(function(&$collection, &$element) {
                $collection[] = $element;
                return true;
            }));
        $this->modelManager
            ->expects($this->any())
            ->method('collectionHasElement')
            ->will($this->returnCallback(function(&$collection, &$element) {
                return in_array($element, $collection, true);
            }));
        $this->modelManager
            ->expects($this->any())
            ->method('collectionClear')
            ->will($this->returnCallback(function(&$collection) {
                return $collection = array();
            }));
        
        
        $this->builder = new FormBuilder('name', null, $this->dispatcher, $this->factory);
        
    }
    
    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->factory = null;
        $this->modelManager = null;
        $this->builder = null;
    }
    
    public function testSortOrder()
    {
        $form = $this->builder->getForm();
        $form->setData(array(1, 2, 3, 4, 5, 6));
        
        $event = new FormEvent($form, array(2, 1, 4, 5));
        
        $listener = new MergeCollectionListener($this->modelManager);
        $listener->onBind($event);
        
        $this->assertEquals($event->getData(), array(2, 1, 4, 5));
    }
}
