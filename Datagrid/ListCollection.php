<?php

namespace Sonata\BaseApplicationBundle\Datagrid;


use Sonata\BaseApplicationBundle\Admin\FieldDescription;
    
class ListCollection
{

    protected $elements = array();

    public function add(FieldDescription $fieldDescription)
    {
        $this->elements[$fieldDescription->getName()] = $fieldDescription;
    }

    public function getElements()
    {
        return $this->elements;
    }

    
}