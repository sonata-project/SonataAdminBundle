<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
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