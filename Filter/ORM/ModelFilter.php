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

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sonata\AdminBundle\Form\Type\BooleanType;

class ModelFilter extends Filter
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $field
     * @param mixed $data
     * @return
     */
    public function filter($queryBuilder, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return;
        }

        if (is_array($data['value'])) {
            $this->handleMultiple($queryBuilder, $alias, $field, $data);
        } else {
            $this->handleScalar($queryBuilder, $alias, $field, $data);
        }
    }

    protected function handleMultiple($queryBuilder, $alias, $field, $data)
    {
        if (count($data['value']) == 0) {
            return;
        }

        if (isset($data['type']) && $data['type'] == BooleanType::TYPE_NO) {
            $this->applyWhere($queryBuilder, $queryBuilder->expr()->notIn(sprintf('%s.%s', $alias, $field), $data['value']));
        } else {
            $this->applyWhere($queryBuilder, $queryBuilder->expr()->in(sprintf('%s.%s', $alias, $field), $data['value']));
        }
    }

    protected function handleScalar($queryBuilder, $alias, $field, $data)
    {
        if (empty($data['value'])) {
            return;
        }

        if (isset($data['type']) && $data['type'] == BooleanType::TYPE_NO) {
            $this->applyWhere($queryBuilder, sprintf('%s.%s != :%s', $alias, $field, $this->getName()));
        } else {
            $this->applyWhere($queryBuilder, sprintf('%s.%s = :%s', $alias, $field, $this->getName()));
        }

        $queryBuilder->setParameter($this->getName(), $data['value']);
    }

    protected function association($queryBuilder, $data)
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

        $alias = 's_'.$this->getName();

        $queryBuilder->leftJoin(sprintf('%s.%s', $queryBuilder->getRootAlias(), $this->getFieldName()), $alias);

        return array($alias, 'id');
    }

    public function getDefaultOptions()
    {
        return array(
            'mapping_type' => false,
            'field_name'   => false,
            'field_type'   => 'entity',
            'field_options' => array(),
            'operator_type' => 'sonata_type_boolean',
            'operator_options' => array(),
        );
    }

    public function getRenderSettings()
    {
        return array('sonata_type_filter_default', array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'operator_type' => $this->getOption('operator_type'),
            'operator_options' => $this->getOption('operator_options'),
            'label'         => $this->getLabel()
        ));
    }
}