<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Filter;

use Sonata\AdminBundle\Admin\FieldDescription;
use Doctrine\ORM\QueryBuilder;

class BooleanFilter extends Filter
{

    public function doFilter(QueryBuilder $queryBuilder, $alias, $field, $value)
    {

        if ($this->getField()->isMultipleChoice()) {

            $values = array();
            foreach ($value as $v) {
                if ($v == 'all') {
                    return;
                }

                $values[] = $v == 'true' ? 1 : 0;
            }

            if (count($values) == 0) {
                return;
            }
            
            $queryBuilder->andWhere($queryBuilder->expr()->in(sprintf('%s.%s',
                $alias,
                $field
            ), $values));

        } else {

            if ($value === null || $value == 'all') {
                return;
            }

            $value      = $value == 'true' ? 1 : 0;
            
            $queryBuilder->andWhere(sprintf('%s.%s = :%s',
                $alias,
                $field,
                $this->getName()
            ));

            $queryBuilder->setParameter($this->getName(), $value);
        }
    }

    protected function configure()
    {

        parent::configure();
    }

    public function getFormField()
    {

        $options = array(
            'choices' => array(
                'all'   => 'all',
                'true'  => 'true',
                'false' => 'false'
            ),
            'required' => false
        );

        $options = array_merge($options, $this->description->getOption('filter_field_options', array()));

        return new \Symfony\Component\Form\ChoiceField(
            $this->getName(),
            $options
        );
    }

}