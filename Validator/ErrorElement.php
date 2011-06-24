<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Validator;

use Symfony\Bundle\FrameworkBundle\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Component\Form\Util\PropertyPath;

class ErrorElement
{
    protected $context;

    protected $group;

    protected $constraintValidatorFactory;

    protected $stack = array();

    protected $propertyPaths = array();

    protected $subject;

    protected $current = '';

    public function __construct($subject, ConstraintValidatorFactory $constraintValidatorFactory, ExecutionContext $context, $group)
    {
        $this->subject = $subject;
        $this->context = $context;
        $this->group   = $group;
        $this->constraintValidatorFactory = $constraintValidatorFactory;
    }

    public function __call($name, array $arguments = array())
    {
        if (substr($name, 0, 6) == 'assert') {
            $this->validate($this->newConstraint(
                substr($name, 6),
                isset($arguments[0]) ? $arguments[0] : array()
            ));
        } else {
            throw new \RunTimeException('Unable to recognize the command');
        }

        return $this;
    }

    public function with($name, $key = false)
    {
        $key = $key ? $name.'.'.$key : $name;
        $this->stack[] = $key;

        $this->current = implode('.', $this->stack);

        if (!isset($this->propertyPaths[$this->current])) {
            $this->propertyPaths[$this->current] = new PropertyPath($this->current);
        }

        return $this;
    }

    public function end()
    {
        array_pop($this->stack);

        $this->current = implode('.', $this->stack);

        return $this;
    }

    protected function validate($constraint)
    {
        $validator  = $this->constraintValidatorFactory->getInstance($constraint);
        $value      = $this->getValue();

        $validator->isValid($value, $constraint);

        $this->context->setPropertyPath($this->propertyPaths[$this->current]);
        $this->context->setGroup($this->group);

        $validator->initialize($this->context);

        if (!$validator->isValid($value, $constraint)) {
            $this->context->addViolation(
                $validator->getMessageTemplate(),
                $validator->getMessageParameters(),
                $value
            );
        }
    }

    /**
     * Return the value linked to
     *
     * @return mixed
     */
    protected function getValue()
    {
        return $this->propertyPaths[$this->current]->getValue($this->subject);
    }

    public function getSubject()
    {
        return $this->subject;
    }

    protected function newConstraint($name, $options)
    {
        if (strpos($name, '\\') !== false && class_exists($name)) {
            $className = (string) $name;
        } else {
            $className = 'Symfony\\Component\\Validator\\Constraints\\'.$name;
        }

        return new $className($options);
    }

    public function addViolation($error)
    {
        $this->context->setPropertyPath($this->propertyPaths[$this->current]);
        $this->context->setGroup($this->group);

        if (!is_array($error)) {
            $this->context->addViolation($error, array(), null);
        } else {
            $this->context->addViolation(
                isset($error[0]) ? $error[0] : 'error',
                isset($error[1]) ? (array)$error[1] : array(),
                isset($error[2]) ? $error[2] : null
            );
        }

        return $this;
    }
}