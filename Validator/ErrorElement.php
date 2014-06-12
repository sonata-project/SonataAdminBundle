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

use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Validator\Constraint;

class ErrorElement
{
    /**
     * @var ExecutionContextInterface
     */
    protected $context;

    /**
     * @var string
     */
    protected $group;

    /**
     * @var ConstraintValidationFactoryInterface
     */
    protected $constraintValidatorFactory;

    protected $stack = array();

    protected $propertyPaths = array();

    protected $subject;

    protected $current;

    protected $basePropertyPath;

    protected $errors = array();

    /**
     * @param mixed                               $subject
     * @param ConstraintValidatorFactoryInterface $constraintValidatorFactory
     * @param ExecutionContextInterface           $context
     * @param string                              $group
     */
    public function __construct($subject, ConstraintValidatorFactoryInterface $constraintValidatorFactory, ExecutionContextInterface $context, $group)
    {
        $this->subject                    = $subject;
        $this->context                    = $context;
        $this->group                      = $group;
        $this->constraintValidatorFactory = $constraintValidatorFactory;

        $this->current          = '';
        $this->basePropertyPath = $this->context->getPropertyPath();
    }

    /**
     * @throws \RunTimeException
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return ErrorElement
     */
    public function __call($name, array $arguments = array())
    {
        if (substr($name, 0, 6) == 'assert') {
            $this->validate($this->newConstraint(substr($name, 6), isset($arguments[0]) ? $arguments[0] : array()));
        } else {
            throw new \RunTimeException('Unable to recognize the command');
        }

        return $this;
    }

    /**
     * @param Constraint $constraint
     *
     * @return ErrorElement
     */
    public function addConstraint(Constraint $constraint)
    {
        $this->validate($constraint);

        return $this;
    }

    /**
     * @param string $name
     * @param bool   $key
     *
     * @return ErrorElement
     */
    public function with($name, $key = false)
    {
        $key           = $key ? $name . '.' . $key : $name;
        $this->stack[] = $key;

        $this->current = implode('.', $this->stack);

        if (!isset($this->propertyPaths[$this->current])) {
            $this->propertyPaths[$this->current] = new PropertyPath($this->current);
        }

        return $this;
    }

    /**
     * @return ErrorElement
     */
    public function end()
    {
        array_pop($this->stack);

        $this->current = implode('.', $this->stack);

        return $this;
    }

    /**
     * @param \Symfony\Component\Validator\Constraint $constraint
     *
     * @return void
     */
    protected function validate(Constraint $constraint)
    {
        $subPath = (string) $this->getCurrentPropertyPath();

        $this->context->validateValue($this->getValue(), $constraint, $subPath, $this->group);
    }

    /**
     * @return string
     */
    public function getFullPropertyPath()
    {
        if ($this->getCurrentPropertyPath()) {
            return sprintf('%s.%s', $this->basePropertyPath, $this->getCurrentPropertyPath());
        } else {
            return $this->basePropertyPath;
        }
    }

    /**
     * Return the value linked to
     *
     * @return mixed
     */
    protected function getValue()
    {
        if ($this->current == '') {
            return $this->subject;
        }

        $propertyAccessor = PropertyAccess::getPropertyAccessor();
        return $propertyAccessor->getValue($this->subject, $this->getCurrentPropertyPath());
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $name
     * @param array  $options
     *
     * @return
     */
    protected function newConstraint($name, array $options = array())
    {
        if (strpos($name, '\\') !== false && class_exists($name)) {
            $className = (string) $name;
        } else {
            $className = 'Symfony\\Component\\Validator\\Constraints\\' . $name;
        }

        return new $className($options);
    }

    /**
     * @return null|PropertyPath
     */
    protected function getCurrentPropertyPath()
    {
        if (!isset($this->propertyPaths[$this->current])) {
            return null; //global error
        }

        return $this->propertyPaths[$this->current];
    }

    /**
     * @param string|array $message
     * @param array        $parameters
     * @param null         $value
     *
     * @return ErrorElement
     */
    public function addViolation($message, $parameters = array(), $value = null)
    {
        if (is_array($message)) {
            $value      = isset($message[2]) ? $message[2] : $value;
            $parameters = isset($message[1]) ? (array) $message[1] : array();
            $message    = isset($message[0]) ? $message[0] : 'error';
        }

        $subPath = (string) $this->getCurrentPropertyPath();

        $this->context->addViolationAt($subPath, $message, $parameters, $value);

        $this->errors[] = array($message, $parameters, $value);

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
