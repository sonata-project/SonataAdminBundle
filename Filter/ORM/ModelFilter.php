<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Filter\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class ModelFilter extends Filter
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $field
     * @param mixed $value
     * @return
     */
    public function filter($queryBuilder, $alias, $field, $value)
    {
        if (is_array($value)) {
            if (count($value) == 0) {
                return;
            }

            $queryBuilder->andWhere($queryBuilder->expr()->in(sprintf('%s.%s', $alias, $field ), $value));
        } else {

            if (empty($value)) {
                return;
            }

            $queryBuilder->andWhere(sprintf('%s.%s = :%s', $alias, $field, $this->getName()));
            $queryBuilder->setParameter($this->getName(), $value);
        }
    }

    protected function association($queryBuilder, $value)
    {
        $types = array(
            ClassMetadataInfo::ONE_TO_ONE,
            ClassMetadataInfo::ONE_TO_MANY,
            ClassMetadataInfo::MANY_TO_MANY,
            ClassMetadataInfo::MANY_TO_ONE,
        );

        if (!in_array($this->getOption('mapping_type'), $types)) {
            throw new \RunTimeException('Invalid mapping type');
        }

        if (!$this->getOption('field_name')) {
            throw new \RunTimeException('please provide a field_name options');
        }

        $queryBuilder->leftJoin(sprintf('%s.%s', $queryBuilder->getRootAlias(), $this->getOption('field_name')), $this->getName());

        return array($this->getOption('field_name'), 'id');
    }

    public function getDefaultOptions()
    {
        return array(
            'mapping_type' => false,
            'field_name' => false
        );
    }
}