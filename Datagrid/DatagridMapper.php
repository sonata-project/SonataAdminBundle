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

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;

/**
 * This class is use to simulate the Form API
 *
 */
class DatagridMapper
{
    protected $datagridBuilder;

    protected $datagrid;

    protected $admin;

    /**
     * @param \Sonata\AdminBundle\Builder\DatagridBuilderInterface $datagridBuilder
     * @param DatagridInterface $datagrid
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     */
    public function __construct(DatagridBuilderInterface $datagridBuilder, DatagridInterface $datagrid, AdminInterface $admin)
    {
        $this->datagridBuilder  = $datagridBuilder;
        $this->datagrid         = $datagrid;
        $this->admin            = $admin;
    }

    /**
     * @throws \RuntimeException
     * @param mixed $name
     * @param mixed $type
     * @param array $fieldDescriptionOptions
     * @return DatagridMapper
     */
    public function add($name, $type = null, array $fieldDescriptionOptions = array())
    {
        if ($name instanceof FieldDescriptionInterface) {
            $fieldDescription = $name;
            $fieldDescription->mergeOptions($fieldDescriptionOptions);
        } else if (is_string($name) && !$this->admin->hasFormFieldDescription($name)) {
            $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
                $this->admin->getClass(),
                $name,
                $fieldDescriptionOptions
            );
        } else {
            throw new \RuntimeException('invalid state');
        }

        // add the field with the DatagridBuilder
        $this->datagridBuilder->addFilter($this->datagrid, $type, $fieldDescription, $this->admin);

        return $this;
    }

    /**
     * @param $name
     * @return
     */
    public function get($name)
    {
        return $this->datagrid->get($name);
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        return $this->datagrid->has($key);
    }

    /**
     * @param string $key
     * @return void
     */
    public function remove($key)
    {
        $this->admin->removeFilterFieldDescription($key);
        $this->datagrid->remove($key);
    }
}
