<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\AbstractListBuilder;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class FakeListBuilder extends AbstractListBuilder
{
    /**
     * {@inheritdoc}
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        // TODO: Implement fixFieldDescription() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseList(array $options = array())
    {
        // TODO: Implement getBaseList() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildField($type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin)
    {
        // TODO: Implement buildField() method.
    }

    /**
     * {@inheritdoc}
     */
    public function addField(FieldDescriptionCollection $list, $type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin)
    {
        // TODO: Implement addField() method.
    }
}
