<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Datagrid;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Mapper\BaseMapper;

/**
 * This class is used to simulate the Form API.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ListMapper extends BaseMapper
{
    /**
     * @var FieldDescriptionCollection
     */
    protected $list;

    /**
     * @param ListBuilderInterface       $listBuilder
     * @param FieldDescriptionCollection $list
     * @param AdminInterface             $admin
     */
    public function __construct(ListBuilderInterface $listBuilder, FieldDescriptionCollection $list, AdminInterface $admin)
    {
        parent::__construct($listBuilder, $admin);
        $this->list = $list;
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
            $routeName = ($this->admin->hasAccess('edit') && $this->admin->hasRoute('edit')) ? 'edit' : 'show';
            $fieldDescriptionOptions['route']['name'] = $routeName;
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
     * @return ListMapper
     */
    public function add($name, $type = null, array $fieldDescriptionOptions = array())
    {
        // Change deprecated inline action "view" to "show"
        if ($name == '_action' && $type == 'actions') {
            if (isset($fieldDescriptionOptions['actions']['view'])) {
                @trigger_error(
                    'Inline action "view" is deprecated since version 2.2.4 and will be removed in 4.0. '
                    .'Use inline action "show" instead.',
                    E_USER_DEPRECATED
                );

                $fieldDescriptionOptions['actions']['show'] = $fieldDescriptionOptions['actions']['view'];

                unset($fieldDescriptionOptions['actions']['view']);
            }
        }

        // Ensure batch and action pseudo-fields are tagged as virtual
        if (in_array($type, array('actions', 'batch', 'select'))) {
            $fieldDescriptionOptions['virtual_field'] = true;
        }

        if ($name instanceof FieldDescriptionInterface) {
            $fieldDescription = $name;
            $fieldDescription->mergeOptions($fieldDescriptionOptions);
        } elseif (is_string($name)) {
            if ($this->admin->hasListFieldDescription($name)) {
                throw new \RuntimeException(sprintf(
                    'Duplicate field name "%s" in list mapper. Names should be unique.',
                    $name
                ));
            }

            $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
                $this->admin->getClass(),
                $name,
                $fieldDescriptionOptions
            );
        } else {
            throw new \RuntimeException(
                'Unknown field name in list mapper. '
                .'Field name should be either of FieldDescriptionInterface interface or string.'
            );
        }

        if ($fieldDescription->getLabel() !== false) {
            $fieldDescription->setOption(
                'label',
                $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'list', 'label')
            );
        }

        // add the field with the FormBuilder
        $this->builder->addField($this->list, $type, $fieldDescription, $this->admin);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->list->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return $this->list->has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $this->admin->removeListFieldDescription($key);
        $this->list->remove($key);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    final public function keys()
    {
        return array_keys($this->list->getElements());
    }

    /**
     * {@inheritdoc}
     */
    public function reorder(array $keys)
    {
        $this->list->reorder($keys);

        return $this;
    }
}
