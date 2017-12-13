<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;

class PostAdmin extends AbstractAdmin
{
    protected $metadataClass = null;

    public function setParentAssociationMapping($associationMapping)
    {
        $this->parentAssociationMapping = $associationMapping;
    }

    public function setClassMetaData($classMetaData)
    {
        $this->classMetaData = $classMetaData;
    }

    public function getClassMetaData()
    {
        if ($this->classMetaData) {
            return $this->classMetaData;
        }

        return parent::getClassMetaData();
    }

    /**
     * @param array $actions
     *
     * @return array
     */
    protected function configureBatchActions($actions)
    {
        $actions['foo'] = [
            'label' => 'action_foo',
        ];
        $actions['bar'] = [];
        $actions['baz'] = [
            'label' => 'action_baz',
            'translation_domain' => 'AcmeAdminBundle',
        ];

        return $actions;
    }
}
