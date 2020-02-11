<?php

declare(strict_types=1);

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
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ListMapper extends BaseMapper
{
    /**
     * @var FieldDescriptionCollection
     */
    protected $list;

    public function __construct(
        ListBuilderInterface $listBuilder,
        FieldDescriptionCollection $list,
        AdminInterface $admin
    ) {
        parent::__construct($listBuilder, $admin);
        $this->list = $list;
    }

    /**
     * @param string      $name
     * @param string|null $type
     *
     * @return $this
     */
    public function addIdentifier($name, $type = null, array $fieldDescriptionOptions = [])
    {
        $fieldDescriptionOptions['identifier'] = true;

        if (!isset($fieldDescriptionOptions['route']['name'])) {
            $routeName = ($this->admin->hasAccess('edit') && $this->admin->hasRoute('edit')) ? 'edit' : 'show';
            $fieldDescriptionOptions['route']['name'] = $routeName;
        }

        if (!isset($fieldDescriptionOptions['route']['parameters'])) {
            $fieldDescriptionOptions['route']['parameters'] = [];
        }

        return $this->add($name, $type, $fieldDescriptionOptions);
    }

    /**
     * @param FieldDescriptionInterface|string $name
     * @param string|null                      $type
     *
     * @throws \LogicException
     *
     * @return $this
     */
    public function add($name, $type = null, array $fieldDescriptionOptions = [])
    {
        // Change deprecated inline action "view" to "show"
        if ('_action' === $name && 'actions' === $type) {
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
        if (\in_array($type, ['actions', 'batch', 'select'], true)) {
            $fieldDescriptionOptions['virtual_field'] = true;
        }

        if (\array_key_exists('identifier', $fieldDescriptionOptions) && !\is_bool($fieldDescriptionOptions['identifier'])) {
            @trigger_error(
                'Passing a non boolean value for the "identifier" option is deprecated since sonata-project/admin-bundle 3.51 and will throw an exception in 4.0.',
                E_USER_DEPRECATED
            );

            $fieldDescriptionOptions['identifier'] = (bool) $fieldDescriptionOptions['identifier'];
            // NEXT_MAJOR: Remove the previous 6 lines and use commented line below it instead
            // throw new \InvalidArgumentException(sprintf('Value for "identifier" option must be boolean, %s given.', gettype($fieldDescriptionOptions['identifier'])));
        }

        if ($name instanceof FieldDescriptionInterface) {
            $fieldDescription = $name;
            $fieldDescription->mergeOptions($fieldDescriptionOptions);
        } elseif (\is_string($name)) {
            if ($this->admin->hasListFieldDescription($name)) {
                throw new \LogicException(sprintf(
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
            throw new \TypeError(
                'Unknown field name in list mapper. '
                .'Field name should be either of FieldDescriptionInterface interface or string.'
            );
        }

        if (null === $fieldDescription->getLabel()) {
            $fieldDescription->setOption(
                'label',
                $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'list', 'label')
            );
        }

        if (isset($fieldDescriptionOptions['header_style'])) {
            @trigger_error(
                'The "header_style" option is deprecated, please, use "header_class" option instead.',
                E_USER_DEPRECATED
            );
        }

        if (!isset($fieldDescriptionOptions['role']) || $this->admin->isGranted($fieldDescriptionOptions['role'])) {
            // add the field with the FormBuilder
            $this->builder->addField($this->list, $type, $fieldDescription, $this->admin);
        }

        return $this;
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

        return $this;
    }

    final public function keys()
    {
        return array_keys($this->list->getElements());
    }

    public function reorder(array $keys)
    {
        $this->list->reorder($keys);

        return $this;
    }
}
