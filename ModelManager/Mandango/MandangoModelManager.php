<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\ModelManager\Mandango;

use Sonata\AdminBundle\ModelManager\ModelManagerInterface;
use Sonata\AdminBundle\ModelManager\Mandango\Admin\MandangoFieldDescription;

/**
 * MandangoModelManager.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MandangoModelManager implements ModelManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getMetadata($class)
    {
        return call_user_func(array($class, 'metadata'));
    }

    /**
     * {@inheritdoc}
     */
    public function hasMetadata($class)
    {
        return \Mandango\Container::get()->getMetadata()->hasClass($class);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewFieldDescriptionInstance($class, $name, array $options = array())
    {
        $metadata = $this->getMetadata($class);

        $fieldDescription = new MandangoFieldDescription();
        $fieldDescription->setName($name);
        $fieldDescription->setOptions($options);

        return $fieldDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function create($object)
    {
        $object->save();
    }

    /**
     * {@inheritdoc}
     */
    public function update($object)
    {
        $object->save();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object)
    {
        $object->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function find($class, $id)
    {
        return call_user_func(array($class, 'find'), $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentFieldDescription($parentAssociationMapping, $class)
    {
        var_dump($parentAssociationMapping, $class);exit();

        $fieldName = $parentAssociationMapping['fieldName'];

        $metadata = $this->getMetadata($class);

        $associatingMapping = $metadata->associationMappings[$parentAssociationMapping];

        $fieldDescription = $this->getNewFieldDescriptionInstance($class, $fieldName);
        $fieldDescription->setName($parentAssociationMapping);
        $fieldDescription->setAssociationMapping($associatingMapping);

        return $fieldDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($class, $alias = 'o')
    {
        return call_user_func(array($class, 'query'));
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityIdentifier($class)
    {
        return 'id';
    }

    /**
     * {@inheritdoc}
     */
    public function batchDelete($class, $idx)
    {
        $repository = call_user_func(array($class, 'repository'));

        $repository->remove(array('_id' => array('$in' => $idx)));
    }

    /**
     * {@inheritdoc}
     */
    public function getModelInstance($class)
    {
        return new $class();
    }
}
