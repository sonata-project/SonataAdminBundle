<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\DataTransformer;

use Prophecy\Promise\ThrowPromise;
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader;
use Sonata\AdminBundle\Form\DataTransformer\ModelsToArrayTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Entity\Form\FooEntity;
use Symfony\Component\Config\Definition\Exception\Exception;

class ModelsToArrayTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ModelManagerInterface
     */
    private $modelManager;

    /**
     * @var ModelChoiceLoader
     */
    private $choiceList;

    private $class;
    private $entity;

    public function setUp()
    {
        $this->modelManager = $this->prophesize('Sonata\AdminBundle\Model\ModelManagerInterface');

        $this->choiceList = $this->prophesize('Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader');

        $this->class = 'Sonata\AdminBundle\Tests\Fixtures\Entity\Form\FooEntity';
        $this->entity = new FooEntity();
    }

    public function testGetIdentifierValues()
    {
        $transformer = new ModelsToArrayTransformer(
            $this->choiceList->reveal(),
            $this->modelManager->reveal(),
            $this->class
        );
        $identityObject = new \stdClass();

        $this->modelManager
            ->getIdentifierValues($this->entity)
            ->willReturn($identityObject);

        $this->assertEquals(
            $this->invokeMethod(
                $transformer,
                'getIdentifierValues',
                array($this->entity)
            ),
            $identityObject,
            'Should return identified values via modelManager'
        );

        $exceptionObject = new Exception('test exception');
        $this->modelManager
            ->getIdentifierValues($this->entity)
            ->will(new ThrowPromise($exceptionObject));

        $this->setExpectedException(
            'InvalidArgumentException',
            'Unable to retrieve the identifier values for entity '.$this->class
        );

        $this->invokeMethod($transformer, 'getIdentifierValues', array($this->entity));
    }

    public function testGetIdentifierValuesHandlesNull()
    {
        $transformer = new ModelsToArrayTransformer(
            $this->choiceList->reveal(),
            $this->modelManager->reveal(),
            $this->class
        );

        $this->setExpectedException(
            'InvalidArgumentException',
            'Expected an entity class, received NULL'
        );

        $this->invokeMethod($transformer, 'getIdentifierValues', array(null));
    }

    /**
     * Invoke a private or protected method for sakes of testing.
     *
     * @link https://jtreminio.com/2013/03/unit-testing-tutorial-part-3-testing-protected-private-methods-coverage-reports-and-crap/#targeting-private%2Fprotected-methods-directly Pulled from post by Juan Treminio
     *
     * @param object $object     to invoke method on
     * @param string $methodName to invoke
     * @param array  $parameters to pass to the method
     *
     * @return mixed the result
     */
    private function invokeMethod($object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
