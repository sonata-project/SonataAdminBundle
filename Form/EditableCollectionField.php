<?php

namespace Sonata\BaseApplicationBundle\Form;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Symfony\Component\Form\Form;
use Symfony\Component\Form\CheckboxField;
use Symfony\Component\Form\FieldInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.com>
 * @author     Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class EditableCollectionField extends Form
{
    /**
     * The prototype for the inner fields
     * @var FieldInterface
     */
    protected $prototype;

    /**
     * Remembers which fields were removed upon binding
     * @var array
     */
    protected $removedFields = array();

    /**
     * Repeats the given field twice to verify the user's input
     *
     * @param FieldInterface $innerField
     */
    public function __construct(FieldInterface $innerField, array $options = array())
    {
        $this->prototype = $innerField;

        parent::__construct($innerField->getKey(), $options);
    }

    
    public function submit($taintedData)
    {
        $this->removedFields = array();

        if (null === $taintedData) {
            $taintedData = array();
        }

        foreach ($this as $name => $field) {
            if (!isset($taintedData[$name]) || array_key_exists('_delete', $taintedData[$name])) {
                $this->remove($name);
                $this->removedFields[] = $name;
            }
        }

        // add new element (+ icon)
        foreach ($taintedData as $name => $value) {
            if (!isset($this[$name])) {
                $this->addField($name, $name);
            }
        }

        parent::submit($taintedData);
    }

    /**
     * Add a new element to the collection
     *
     * @param string $key
     * @param string $propertyPath
     */
    public function addField($key, $propertyPath)
    {
        $this->add($this->newfield($key, $propertyPath));
    }

    /**
     *
     * @return the FieldGroup prototype used to generate the collection
     */
    public function getPrototype()
    {
        return $this->prototype;
    }

    protected function newField($key, $propertyPath)
    {
        $field = clone $this->prototype;
    
        $field->setKey($key);
        $field->setPropertyPath(null === $propertyPath ? null : '['.$propertyPath.']');
        
        return $field;
    }

    public function setData($collection)
    {
        if (!is_array($collection) && !$collection instanceof \Traversable) {
            throw new UnexpectedTypeException($collection, 'array or \Traversable');
        }

        foreach ($collection as $name => $value) {
            $this->add($this->newField($name, $name));
        }

        parent::setData($collection);
    }

    protected function writeObject(&$objectOrArray)
    {

        parent::writeObject($objectOrArray);

        foreach ($this->removedFields as $name) {
            unset($objectOrArray[$name]);
        }
    }
}