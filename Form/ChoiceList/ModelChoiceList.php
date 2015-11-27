<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form\ChoiceList;

use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;

/**
 * Class ModelChoiceList.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ModelChoiceList extends ArrayChoiceList
{
    /**
     * @var ModelChoiceListAdapter
     */
    private $adapter;

    /**
     * @param ModelManagerInterface $modelManager
     * @param string                $class
     * @param null                  $property
     * @param null                  $query
     * @param array                 $choices
     */
    public function __construct(ModelManagerInterface $modelManager, $class, $property = null, $query = null, $choices = array())
    {
        $this->adapter = new ModelChoiceListAdapter($modelManager, $class, $property, $query, $choices);

        parent::__construct($this->load($choices));
    }

    /**
     * @see ModelChoiceListAdapter::load
     *
     * @param $choices
     *
     * @return array An array of choices
     */
    protected function load($choices)
    {
        return $this->adapter->load($choices);
    }

    /**
     * @see ModelChoiceListAdapter::getIdentifier
     *
     * @return array
     */
    public function getIdentifier()
    {
        return $this->adapter->getIdentifier();
    }

    /**
     * @see ModelChoiceListAdapter::getEntities
     *
     * @return array An array of entities
     */
    public function getEntities()
    {
        return $this->adapter->getEntities();
    }

    /**
     * @see ModelChoiceListAdapter::getEntity
     *
     * @param string $key
     *
     * @return object The matching entity
     */
    public function getEntity($key)
    {
        return $this->adapter->getEntity($key);
    }

    /**
     * @see ModelChoiceListAdapter::getIdentifierValues
     *
     * @param object $entity
     *
     * @return mixed
     */
    public function getIdentifierValues($entity)
    {
        return $this->getIdentifierValues($entity);
    }

    /**
     * @see ModelChoiceListAdapter::getIdentifierValues
     *
     * @return ModelManagerInterface
     */
    public function getModelManager()
    {
        return $this->adapter->getModelManager();
    }

    /**
     * @see ModelChoiceListAdapter::getIdentifierValues
     *
     * @return string
     */
    public function getClass()
    {
        return $this->adapter->getClass();
    }
}
