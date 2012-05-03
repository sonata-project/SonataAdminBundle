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
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
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

    /**
     * @param \Sonata\AdminBundle\Builder\ListBuilderInterface     $listBuilder
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionCollection $list
     * @param \Sonata\AdminBundle\Admin\AdminInterface             $admin
     */
    public function __construct(ListBuilderInterface $listBuilder, FieldDescriptionCollection $list, AdminInterface $admin)
    {
        $this->listBuilder = $listBuilder;
        $this->list        = $list;
        $this->admin       = $admin;
    }

    /**
     * @param string $name
     * @param null   $type
     * @param array  $fieldDescriptionOptions
     *
     * @return ListMapper
     */
    public function addIdentifier($name, $type = null, array $fieldDescriptionOptions = array())
    {
        $fieldDescriptionOptions['identifier'] = true;

        if (!isset($fieldDescriptionOptions['route']['name'])) {
            $fieldDescriptionOptions['route']['name'] = 'edit';
        }

        if (!isset($fieldDescriptionOptions['route']['parameters'])) {
            $fieldDescriptionOptions['route']['parameters'] = array();
        }

        return $this->add($name, $type, $fieldDescriptionOptions);
    }

    /**
     * @throws \RuntimeException
     *
     * @param mixed $name
     * @param mixed $type
     * @param array $fieldDescriptionOptions
     *
     * @return \Sonata\AdminBundle\Datagrid\ListMapper
     */
    public function add($name, $type = null, array $fieldDescriptionOptions = array())
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
        } else {
            throw new \RuntimeException('Unknown or duplicate field name in list mapper. Field name should be either of FieldDescriptionInterface interface or string. Names should be unique.');
        }

        if (!$fieldDescription->getLabel()) {
            $fieldDescription->setOption('label', $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'list', 'label'));
        }

        // add the field with the FormBuilder
        $this->listBuilder->addField($this->list, $type, $fieldDescription, $this->admin);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    public function get($name)
    {
        return $this->list->get($name);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->list->has($key);
    }

    /**
     * @param  string $key
     *
     * @return \Sonata\AdminBundle\Datagrid\ListMapper
     */
    public function remove($key)
    {
        $this->admin->removeListFieldDescription($key);
        $this->list->remove($key);

        return $this;
    }

    /**
     * @param array $keys field names
     *
     * @return \Sonata\AdminBundle\Datagrid\ListMapper
     */
    public function reorder(array $keys)
    {
        $this->list->reorder($keys);

        return $this;
    }
}
