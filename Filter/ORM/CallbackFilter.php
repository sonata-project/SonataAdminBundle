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

class CallbackFilter extends Filter
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param mixed $data
     * @return array
     */
    protected function association($queryBuilder, $data)
    {
        return array($queryBuilder->getRootAlias(), false);
    }

    /**
     * @throws \RuntimeException
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $field
     * @param string $data
     * @return void
     */
    public function filter($queryBuilder, $alias, $field, $data)
    {
        if (!is_callable($this->getOption('callback'))) {
            throw new \RuntimeException(sprintf('Please provide a valid callback option "filter" for field "%s"', $this->getName()));
        }

        call_user_func($this->getOption('callback'), $queryBuilder, $alias, $field, $data);
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return array(
            'callback'    => null,
            'field_type'  => 'text',
            'operator_type' => 'hidden',
            'operator_options' => array()
        );
    }

    public function getRenderSettings()
    {
        return array('sonata_type_filter_default', array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'operator_type' => $this->getOption('operator_type'),
            'operator_options' => $this->getOption('operator_options'),
        ));
    }
}