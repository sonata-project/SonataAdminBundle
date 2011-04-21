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
use Sonata\AdminBundle\Datagrid\ListCollection;
use Sonata\AdminBundle\Builder\ListBuilderInterface;

/**
 * This class is used to simulate the Form API
 *
 */
class ListMapper
{
    protected $listBuilder;

    protected $list;

    protected $admin;

    public function __construct(ListBuilderInterface $listBuilder, ListCollection $list, AdminInterface $admin)
    {
        $this->listBuilder  = $listBuilder;
        $this->list         = $list;
        $this->admin        = $admin;
    }

    /**
     * @throws \RuntimeException
     * @param string $name
     * @param array $fieldDescriptionOptions
     * @return
     */
    public function add($name, array $fieldDescriptionOptions = array())
    {
        if ($name instanceof FieldDescriptionInterface) {

            $fieldDescription = $name;
            $fieldDescription->mergeOptions($fieldDescriptionOptions);

        } else if (is_string($name) && !$this->admin->hasListFieldDescription($name)) {

            $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
                $this->admin->getClass(),
                $name,
                $fieldDescriptionOptions
            );

            $this->listBuilder->fixFieldDescription($this->admin, $fieldDescription, $fieldDescriptionOptions);
            $this->admin->addListFieldDescription($name, $fieldDescription);

        } else if (is_string($name) && $this->admin->hasListFieldDescription($name)) {
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

    /**
     * @param string $name
     * @return array
     */
    public function get($name)
    {
        return $this->list->get($name);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->list->has($key);
    }

    /**
     * @param  $key
     * @return void
     */
    public function remove($key)
    {
        $this->admin->removeListFieldDescription($key);
        $this->list->remove($key);
    }
}