<?php

namespace Sonata\BaseApplicationBundle\Datagrid;

use Sonata\BaseApplicationBundle\Admin\Admin;
use Sonata\BaseApplicationBundle\Admin\FieldDescription;
use Sonata\BaseApplicationBundle\Datagrid\ListCollection;
use Sonata\BaseApplicationBundle\Builder\ListBuilderInterface;

/**
 * This class is use to simulate the Form API
 *
 */
class ListMapper
{
    protected $listBuilder;

    protected $list;

    protected $admin;

    public function __construct(ListBuilderInterface $listBuilder, ListCollection $list, Admin $admin)
    {
        $this->listBuilder = $listBuilder;
        $this->list = $list;
        $this->admin = $admin;
    }

    public function add($name, array $fieldDescriptionOptions = array())
    {

        if ($name instanceof FieldDescription) {

            $fieldDescription = $name;
            $fieldDescription->mergeOptions($fieldDescriptionOptions);

        } else if (is_string($name) && !$this->admin->hasFormFieldDescription($name)) {

            $fieldDescription = new FieldDescription;
            $fieldDescription->setOptions($fieldDescriptionOptions);
            $fieldDescription->setName($name);

            $this->listBuilder->fixFieldDescription($this->admin, $fieldDescription, $fieldDescriptionOptions);
            $this->admin->addListFieldDescription($name, $fieldDescription);

        } else if (is_string($name) && $this->admin->hasFormFieldDescription($name)) {
            $fieldDescription = $this->admin->getFormFieldDescription($name);
        } else {

            throw new \RuntimeException('invalid state');
        }

        // add the field with the FormBuilder
        return $this->listBuilder->addField(
            $this->list,
            $fieldDescription
        );
    }

    public function get($name)
    {
        return $this->list->get($name);
    }

    public function has($key)
    {
        return $this->list->has($key);
    }

    public function remove($key)
    {
        $this->admin->removeListFieldDescription($key);
        $this->list->remove($key);
    }
}