<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\ModelManager;

interface ModelManagerInterface
{
    /**
     * Returns true if the model has a relation
     *
     * @abstract
     * @param string $name
     * @return booleab
     */
    function hasMetadata($name);

    /**
     *
     * @param string $name
     * @return \Doctrine\ORM\Mapping\ClassMetadataInfo
     */
    function getMetadata($name);

    /**
     * Returns a new FieldDescription
     *
     * @abstract
     * @param string $class
     * @param string $name
     * @param array $options
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    function getNewFieldDescriptionInstance($class, $name, array $options = array());

    /**
     * @param $object
     * @return void
     */
    function create($object);

    /**
     * @param object $object
     * @return void
     */
    function update($object);

    /**
     * @param object $object
     * @return void
     */
    function delete($object);

    /**
     * @param string $class
     * @param string integer
     * @return object
     */
    function find($class, $id);

    /**
     * @param string $class classname
     * @param array $idx
     * @return void
     */
    function batchDelete($class, $idx);

    /**
     * @abstract
     * @param  $parentAssociationMapping
     * @param  $class
     * @return void
     */
    function getParentFieldDescription($parentAssociationMapping, $class);

    /**
     * @abstract
     * @param string $alias
     * @return a query instance
     */
    function createQuery($class, $alias = 'o');

    /**
     * @abstract
     * @param string $class
     * @return string
     */
    function getEntityIdentifier($class);

    /**
     * @abstract
     * @param string $class
     * @return void
     */
    function getModelInstance($class);
}
