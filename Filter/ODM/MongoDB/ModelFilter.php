<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Filter\ODM\MongoDB;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sonata\AdminBundle\Form\Type\BooleanType;

/**
 * @todo Support multiple values and Document with non-default strategy for ID
 */
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

    /**
     *
     * @param $queryBuilder
     * @param type $alias
     * @param type $field
     * @param type $data
     * @return type 
     */
    protected function handleMultiple($queryBuilder, $alias, $field, $data)
    {
        if (count($data['value']) == 0) {
            return;
        }

        throw new \Exception('Not yet implemented');
    }

    protected function handleScalar($queryBuilder, $alias, $field, $data)
    {

        if (empty($data['value'])) {
            return;
        }

        if (isset($data['type']) && $data['type'] == BooleanType::TYPE_NO) {
            
            $queryBuilder->field($field . '.$id')->notEqual(new \MongoId($data['value']));
        } else {
            $queryBuilder->field($field . '.$id')->equals(new \MongoId($data['value']));
        }
    }

    public function getDefaultOptions()
    {
        return array(
            'mapping_type' => false,
            'field_name'   => false,
            'field_type'   => 'document',
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