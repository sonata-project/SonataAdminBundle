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

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;

class PostAdmin extends AbstractAdmin
{
    protected $metadataClass;

    public function setParentAssociationMapping($associationMapping): void
    {
        $this->parentAssociationMapping = $associationMapping;
    }

    public function setClassMetaData($classMetaData): void
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
