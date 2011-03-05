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
namespace Sonata\AdminBundle\Datagrid;


use Sonata\AdminBundle\Admin\FieldDescription;
    
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

    public function has($name)
    {
        return array_key_exists($name, $this->elements);
    }

    public function get($name)
    {
        if ($this->has($name)) {
            return $this->elements[$name];
        }

        throw new \InvalidArgumentException(sprintf('Element "%s" does not exist.', $name));
    }
}