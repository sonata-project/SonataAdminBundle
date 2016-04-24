<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Validator;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorInterface as LegacyValidatorInterface;

class ValidatorWrapper implements LegacyValidatorInterface
{
    /* @var mixed ValidatorInterface|LegacyValidatorInterface $validator */
    private $validator;

    /**
     * ValidatorWrapper constructor.
     *
     * @param mixed ValidatorInterface|LegacyValidatorInterface $validator
     */
    public function __construct($validator)
    {
        if (!$validator instanceof ValidatorInterface && !$validator instanceof LegacyValidatorInterface) {
            $message = 'Argument 1 must be an instance of '.
                'Symfony\Component\Validator\Validator\ValidatorInterface '.
                'or Symfony\Component\Validator\ValidatorInterface';

            throw new \InvalidArgumentException($message);
        }

        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, $groups = null, $traverse = false, $deep = false)
    {
        return $this->validator->validate($value, $groups, $traverse, $deep);
    }

    /**
     * {@inheritdoc}
     */
    public function validateProperty($containingValue, $property, $groups = null)
    {
        return $this->validator->validateProperty($containingValue, $property, $groups);
    }

    /**
     * {@inheritdoc}
     */
    public function validatePropertyValue($containingValue, $property, $value, $groups = null)
    {
        return $this->validator->validatePropertyValue($containingValue, $property, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function validateValue($value, $constraints, $groups = null)
    {
        if ($this->validator instanceof LegacyValidatorInterface) {
            return $this->validator->validateValue($value, $constraints, $groups);
        } else {
            return $this->validator->validate($value, $constraints, $groups);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFactory()
    {
        return $this->validator->getMetadataFactory();
    }
}
