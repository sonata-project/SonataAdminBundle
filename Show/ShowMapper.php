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
namespace Sonata\AdminBundle\Show;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;

/**
 * This class is used to simulate the Form API
 *
 */
class ShowMapper
{
    protected $showBuilder;

    protected $list;

    protected $admin;

    protected $currentGroup;

    /**
     * @param \Sonata\AdminBundle\Builder\ShowBuilderInterface     $showBuilder
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionCollection $list
     * @param \Sonata\AdminBundle\Admin\AdminInterface             $admin
     */
    public function __construct(ShowBuilderInterface $showBuilder, FieldDescriptionCollection $list, AdminInterface $admin)
    {
        $this->showBuilder = $showBuilder;
        $this->list        = $list;
        $this->admin       = $admin;
    }

    /**
     * @throws \RuntimeException
     *
     * @param mixed $name
     * @param mixed $type
     * @param array $fieldDescriptionOptions
     *
     * @return \Sonata\AdminBundle\Show\ShowMapper
     */
    public function add($name, $type = null, array $fieldDescriptionOptions = array())
    {
        if (!$this->currentGroup) {
            $this->with($this->admin->getLabel());
        }

        $fieldKey = ($name instanceof FieldDescriptionInterface) ? $name->getName() : $name;

        $formGroups = $this->admin->getShowGroups();
        $formGroups[$this->currentGroup]['fields'][$fieldKey] = $fieldKey;
        $this->admin->setShowGroups($formGroups);


        if ($name instanceof FieldDescriptionInterface) {
            $fieldDescription = $name;
            $fieldDescription->mergeOptions($fieldDescriptionOptions);
        } else if (is_string($name) && !$this->admin->hasShowFieldDescription($name)) {
            $fieldDescription = $this->admin->getModelManager()->getNewFieldDescriptionInstance(
                $this->admin->getClass(),
                $name,
                $fieldDescriptionOptions
            );
        } else {
            throw new \RuntimeException('invalid state');
        }

        if (!$fieldDescription->getLabel()) {
            $fieldDescription->setOption('label', $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'show', 'label'));
        }

        $fieldDescription->setOption('safe', $fieldDescription->getOption('safe', false));

        // add the field with the FormBuilder
        $this->showBuilder->addField($this->list, $type, $fieldDescription, $this->admin);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return array
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
     * @param string $key
     *
     * @return \Sonata\AdminBundle\Show\ShowMapper
     */
    public function remove($key)
    {
        $this->admin->removeShowFieldDescription($key);
        $this->list->remove($key);

        return $this;
    }

    /**
     * @param array $keys field names
     *
     * @return \Sonata\AdminBundle\Show\ShowMapper
     */
    public function reorder(array $keys)
    {
        if (!$this->currentGroup) {
            $this->with($this->admin->getLabel());
        }

        $this->admin->reorderShowGroup($this->currentGroup, $keys);

        return $this;
    }

    /**
     * @param string $name
     * @param array  $options
     *
     * @return \Sonata\AdminBundle\Show\ShowMapper
     */
    public function with($name, array $options = array())
    {
        $showGroups = $this->admin->getShowGroups();
        if (!isset($showGroups[$name])) {
            $showGroups[$name] = array_merge(array(
                'collapsed' => false,
                'fields'    => array()
            ), $options);
        }

        $this->admin->setShowGroups($showGroups);

        $this->currentGroup = $name;

        return $this;
    }

    /**
     * @return \Sonata\AdminBundle\Show\ShowMapper
     */
    public function end()
    {
        $this->currentGroup = null;

        return $this;
    }
}
