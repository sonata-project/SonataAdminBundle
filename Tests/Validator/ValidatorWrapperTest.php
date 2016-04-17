<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Validator;

use Sonata\AdminBundle\Validator\ValidatorWrapper;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Test for ValidatorWrapper.
 *
 * @author Alfonso Machado <email@alfonsomachado.com>
 *
 * @group validator-wrapper
 */
class ValidatorWrapperTest extends \PHPUnit_Framework_TestCase
{
    const RECURSIVE_VALIDATOR = 'Symfony\Component\Validator\Validator\RecursiveValidator';
    const VALIDATOR_INTERFACE = 'Symfony\Component\Validator\ValidatorInterface';

    public function testConstructor()
    {
        $wrappedValidator = $this->prophesize('OtherInterface');

        $this->setExpectedException('\InvalidArgumentException');

        $validator = new ValidatorWrapper($wrappedValidator->reveal());
    }

    /**
     * @dataProvider validatorProvider
     */
    public function testValidate($validatorInterface)
    {
        $value = 'string_to_validate';
        $violations = new ConstraintViolationList();
        $wrappedValidator = $this->prophesize($validatorInterface);
        $wrappedValidator->validate($value, null, false, false)->willReturn($violations);
        $validator = new ValidatorWrapper($wrappedValidator->reveal());

        $this->assertSame(0, count($validator->validate($value)));
    }

    /**
     * @dataProvider validatorProvider
     */
    public function testValidateProperty($validatorInterface)
    {
        $value = 'string_to_validate';
        $property = 'property';
        $violations = new ConstraintViolationList();
        $wrappedValidator = $this->prophesize($validatorInterface);
        $wrappedValidator->validateProperty($value, $property, null)->willReturn($violations);
        $validator = new ValidatorWrapper($wrappedValidator->reveal());

        $this->assertSame(0, count($validator->validateProperty($value, $property)));
    }

    /**
     * @dataProvider validatorProvider
     */
    public function testValidatePropertyValue($validatorInterface)
    {
        $value = 'string_to_validate';
        $property = 'property';
        $violations = new ConstraintViolationList();
        $wrappedValidator = $this->prophesize($validatorInterface);
        $wrappedValidator->validatePropertyValue($value, $property, $value, null)->willReturn($violations);
        $validator = new ValidatorWrapper($wrappedValidator->reveal());

        $this->assertSame(0, count($validator->validatePropertyValue($value, $property, $value)));
    }

    /**
     * @dataProvider validatorProvider
     */
    public function testValidateValue($validatorInterface)
    {
        $value = 'string_to_validate';
        $constraint = $this->prophesize('Symfony\Component\Validator\Constraint')->reveal();
        $violations = new ConstraintViolationList();
        $wrappedValidator = $this->prophesize($validatorInterface);
        if (
            $validatorInterface === self::RECURSIVE_VALIDATOR &&
            !method_exists(self::RECURSIVE_VALIDATOR, 'validateValue')
        ) {
            $wrappedValidator->validate($value, $constraint, $value, null)->willReturn($violations);
        } else {
            $wrappedValidator->validateValue($value, $constraint, $value, null)->willReturn($violations);
        }
        $validator = new ValidatorWrapper($wrappedValidator->reveal());

        $this->assertSame(0, count($validator->validateValue($value, $constraint, $value)));
    }

    /**
     * @dataProvider validatorProvider
     */
    public function testGetMetadataFactory($validatorInterface)
    {
        if (
            $validatorInterface === self::RECURSIVE_VALIDATOR &&
            !method_exists(self::RECURSIVE_VALIDATOR, 'getMetadataFactory')
        ) {
            $this->markTestSkipped(
                'You are testing Symfony 2.5 and 2.6 that not have support for LegacyValidator'
            );
        }

        $metadataFactory = $this->prophesize('Symfony\Component\Validato\MetadataFactoryInterface')->reveal();
        $wrappedValidator = $this->prophesize($validatorInterface);
        $wrappedValidator->getMetadataFactory()->willReturn($metadataFactory);
        $validator = new ValidatorWrapper($wrappedValidator->reveal());

        $this->assertSame($metadataFactory, $validator->getMetadataFactory());
    }

    public function validatorProvider()
    {
        if (class_exists(self::RECURSIVE_VALIDATOR)) {
            return array(
                array(self::VALIDATOR_INTERFACE),
                array(self::RECURSIVE_VALIDATOR),
            );
        } else {
            return array(
                array(self::VALIDATOR_INTERFACE),
            );
        }
    }
}
