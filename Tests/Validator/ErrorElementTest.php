<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\AdminBundle\Tests\Validator;

use Sonata\AdminBundle\Validator\ErrorElement;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Test for ErrorElement
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ErrorElementTest extends \PHPUnit_Framework_TestCase
{
    private $errorElement;
    private $context;
    private $subject;

    protected function setUp()
    {
        $constraintValidatorFactory = $this->getMock('Symfony\Component\Validator\ConstraintValidatorFactoryInterface');

        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContextInterface');
        $this->context->expects($this->once())
                ->method('getPropertyPath')
                ->will($this->returnValue('bar'));

        $this->subject = new Foo();

        $this->errorElement = new ErrorElement($this->subject, $constraintValidatorFactory, $this->context, 'foo_admin');
    }

    public function testGetSubject()
    {
        $this->assertEquals($this->subject, $this->errorElement->getSubject());
    }

    public function testGetErrorsEmpty()
    {
        $this->assertEquals(array(), $this->errorElement->getErrors());
    }

    public function testGetErrors()
    {
        $this->errorElement->addViolation('Foo error message', array('bar_param'=>'bar_param_lvalue'), 'BAR');
        $this->assertEquals(array(array('Foo error message', array('bar_param'=>'bar_param_lvalue'), 'BAR')), $this->errorElement->getErrors());
    }

    public function testAddViolation()
    {
        $this->errorElement->addViolation(array('Foo error message', array('bar_param'=>'bar_param_lvalue'), 'BAR'));
        $this->assertEquals(array(array('Foo error message', array('bar_param'=>'bar_param_lvalue'), 'BAR')), $this->errorElement->getErrors());
    }

    public function testAddConstraint()
    {
        $constraint = new NotNull();

        $this->context->expects($this->once())
            ->method('validateValue')
            ->with($this->equalTo($this->subject), $this->equalTo($constraint), $this->equalTo(''), $this->equalTo('foo_admin'))
            ->will($this->returnValue(null));

        $this->errorElement->addConstraint($constraint);
    }

    public function testWith()
    {
        $constraint = new NotNull();

        $this->context->expects($this->once())
            ->method('validateValue')
            ->with($this->equalTo(null), $this->equalTo($constraint), $this->equalTo('bar'), $this->equalTo('foo_admin'))
            ->will($this->returnValue(null));

        $this->errorElement->with('bar');
        $this->errorElement->addConstraint($constraint);
        $this->errorElement->end();
    }

    public function testCall()
    {
        $constraint = new NotNull();

        $this->context->expects($this->once())
            ->method('validateValue')
            ->with($this->equalTo(null), $this->equalTo($constraint), $this->equalTo('bar'), $this->equalTo('foo_admin'))
            ->will($this->returnValue(null));

        $this->errorElement->with('bar');
        $this->errorElement->assertNotNull();
        $this->errorElement->end();
    }

    public function testCallException()
    {
        $this->setExpectedException('RuntimeException', 'Unable to recognize the command');

        $this->errorElement->with('bar');
        $this->errorElement->baz();
    }

    public function testGetFullPropertyPath()
    {
        $this->errorElement->with('baz');
        $this->assertEquals('bar.baz', $this->errorElement->getFullPropertyPath());
        $this->errorElement->end();

        $this->assertEquals('bar', $this->errorElement->getFullPropertyPath());
    }

    public function testFluidInterface()
    {
        $constraint = new NotNull();

        $this->context->expects($this->any())
            ->method('validateValue')
            ->with($this->equalTo($this->subject), $this->equalTo($constraint), $this->equalTo(''), $this->equalTo('foo_admin'))
            ->will($this->returnValue(null));

        $this->assertEquals($this->errorElement, $this->errorElement->with('baz'));
        $this->assertEquals($this->errorElement, $this->errorElement->end());
        $this->assertEquals($this->errorElement, $this->errorElement->addViolation('Foo error message', array('bar_param'=>'bar_param_lvalue'), 'BAR'));
        $this->assertEquals($this->errorElement, $this->errorElement->addConstraint($constraint));
        $this->assertEquals($this->errorElement, $this->errorElement->assertNotNull());
    }
}
