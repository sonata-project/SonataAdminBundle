<?php

namespace Bundle\Sonata\BaseApplicationBundle\Form;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Symfony\Component\Form\FieldGroup;
use Symfony\Component\Form\CheckboxField;
use Symfony\Component\Form\TextField;
use Symfony\Component\Form\RecursiveFieldIterator;
use Symfony\Component\Form\FieldInterface;

/**
 * @author     Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class EditableFieldGroup extends FieldGroup
{

    /**
     * @inheritDoc
     */
    public function __construct($key, array $options = array())
    {

        $this->add(new CheckboxField('_delete'));

        parent::__construct($key, $options);
    }

    /**
     * @inheritDoc
     */
    protected function updateFromObject(&$objectOrArray)
    {
        $iterator = new RecursiveFieldIterator($this);
        $iterator = new \RecursiveIteratorIterator($iterator);

        foreach ($iterator as $field) {
            if($field->getKey() == '_delete') {
                continue;
            }

            $field->updateFromProperty($objectOrArray);
        }
    }

    /**
     * @inheritDoc
     */
    protected function updateObject(&$objectOrArray)
    {
        $iterator = new RecursiveFieldIterator($this);
        $iterator = new \RecursiveIteratorIterator($iterator);

        foreach ($iterator as $field) {
            if($field->getKey() == '_delete') {
                continue;
            }

            $field->updateProperty($objectOrArray);
        }
    }

}