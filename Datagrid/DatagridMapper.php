<?php

namespace Sonata\BaseApplicationBundle\Datagrid;

use Sonata\BaseApplicationBundle\Admin\Admin;
use Sonata\BaseApplicationBundle\Admin\FieldDescription;
use Sonata\BaseApplicationBundle\Datagrid\Datagrid;
use Sonata\BaseApplicationBundle\Builder\DatagridBuilderInterface;

/**
 * This class is use to simulate the Form API
 *
 */
class DatagridMapper
{
    protected $datagridBuilder;

    protected $datagrid;

    protected $admin;

    public function __construct(DatagridBuilderInterface $datagridBuilder, Datagrid $datagrid, Admin $admin)
    {
        $this->datagridBuilder  = $datagridBuilder;
        $this->datagrid         = $datagrid;
        $this->admin            = $admin;
    }

    public function add($name, array $fieldDescriptionOptions = array())
    {
        if ($name instanceof FieldDescription) {

            $fieldDescription = $name;
            $fieldDescription->mergeOptions($fieldDescriptionOptions);

        } else if (is_string($name) && !$this->admin->hasFormFieldDescription($name)) {

            $fieldDescription = new FieldDescription;
            $fieldDescription->setName($name);
            $fieldDescription->setOptions($fieldDescriptionOptions);
            
            $this->datagridBuilder->fixFieldDescription($this->admin, $fieldDescription, $fieldDescriptionOptions);
            $this->admin->addListFieldDescription($name, $fieldDescription);

        } else if (is_string($name) && $this->admin->hasFormFieldDescription($name)) {
            $fieldDescription = $this->admin->getFormFieldDescription($name);
        } else {

            throw new \RuntimeException('invalid state');
        }

        // add the field with the FormBuilder
        return $this->datagridBuilder->addFilter(
            $this->datagrid,
            $fieldDescription
        );
    }

    public function get($name)
    {
        return $this->datagrid->get($name);
    }

    public function has($key)
    {
        return $this->datagrid->has($key);
    }

    public function remove($key)
    {
        $this->admin->removeFilterFieldDescription($key);
        $this->datagrid->remove($key);
    }
}