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

    public function __construct(ShowBuilderInterface $showBuilder, FieldDescriptionCollection $list, AdminInterface $admin)
    {
        $this->showBuilder  = $showBuilder;
        $this->list         = $list;
        $this->admin        = $admin;
    }

    /**
     * @throws \RuntimeException
     * @param string $name
     * @param array $fieldDescriptionOptions
     * @return \Sonata\AdminBundle\Datagrid\ListMapper
     */
    public function add($name, $type = null, array $fieldDescriptionOptions = array())
    {
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

        // add the field with the FormBuilder
        $this->showBuilder->addField($this->list, $type, $fieldDescription, $this->admin);

        return $this;
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
        $this->admin->removeShowFieldDescription($key);
        $this->list->remove($key);
    }
}