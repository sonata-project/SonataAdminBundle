<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Test\Validator\Constraints;

use Sonata\AdminBundle\Validator\Constraints\InlineConstraint;

/**
 * Test for InlineConstraint.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class InlineConstraintTest extends \PHPUnit_Framework_TestCase
{
    public function testValidatedBy()
    {
        $constraint = new InlineConstraint(array('service' => 'foo', 'method' => 'bar'));
        $this->assertSame('sonata.admin.validator.inline', $constraint->validatedBy());
    }

    public function testIsClosure()
    {
        $constraint = new InlineConstraint(array('service' => 'foo', 'method' => 'bar'));
        $this->assertFalse($constraint->isClosure());

        $constraint = new InlineConstraint(array('service' => 'foo', 'method' => function () {}));
        $this->assertTrue($constraint->isClosure());
    }

    public function testGetClosure()
    {
        $closure = function () {return 'FOO';};

        $constraint = new InlineConstraint(array('service' => 'foo', 'method' => $closure));
        $this->assertSame($closure, $constraint->getClosure());
    }

    public function testGetTargets()
    {
        $constraint = new InlineConstraint(array('service' => 'foo', 'method' => 'bar'));
        $this->assertSame(InlineConstraint::CLASS_CONSTRAINT, $constraint->getTargets());
    }

    public function testGetRequiredOptions()
    {
        $constraint = new InlineConstraint(array('service' => 'foo', 'method' => 'bar'));
        $this->assertSame(array('service', 'method'), $constraint->getRequiredOptions());
    }

    public function testGetMethod()
    {
        $constraint = new InlineConstraint(array('service' => 'foo', 'method' => 'bar'));
        $this->assertSame('bar', $constraint->getMethod());
    }

    public function testGetService()
    {
        $constraint = new InlineConstraint(array('service' => 'foo', 'method' => 'bar'));
        $this->assertSame('foo', $constraint->getService());
    }
}
