<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\Sonata\BaseApplicationBundle\Filter;


class ChoiceFilter extends Filter
{

    public function filter($query_builder, $alias, $field, $value)
    {


        if($this->getField()->isMultipleChoice()) {

            if(in_array('all', $value)) {
                return;
            }

            if(count($value) == 0) {
                return;
            }

            $query_builder->andWhere($query_builder->expr()->in(sprintf('%s.%s',
                $alias,
                $field
            ), $value));

        } else {

            if ($value === null || $value == 'all') {
                return;
            }

            $query_builder->andWhere(sprintf('%s.%s = :%s',
                $alias,
                $field,
                $this->getName()
            ));

            $query_builder->setParameter($this->getName(), $value);
        }
    }

    protected function configure()
    {

        parent::configure();
    }

    public function getFormField()
    {
        return new \Symfony\Component\Form\ChoiceField(
            $this->getName(),
            $this->description['filter_field_options']
        );
    }
}