<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BaseApplicationBundle\Filter;

use Sonata\BaseApplicationBundle\Admin\FieldDescription;
use Doctrine\ORM\QueryBuilder;

class ChoiceFilter extends Filter
{

    public function filter(QueryBuilder $queryBuilder, $alias, $field, $value)
    {


        if ($this->getField()->isMultipleChoice()) {

            if (in_array('all', $value)) {
                return;
            }

            if (count($value) == 0) {
                return;
            }

            $queryBuilder->andWhere($queryBuilder->expr()->in(sprintf('%s.%s',
                $alias,
                $field
            ), $value));

        } else {

            if ($value === null || $value == 'all') {
                return;
            }

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
        return new \Symfony\Component\Form\ChoiceField(
            $this->getName(),
            $this->description->getOption('filter_field_options', array('required' => false))
        );
    }
}