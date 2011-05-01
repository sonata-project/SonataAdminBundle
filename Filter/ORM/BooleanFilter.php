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
use Symfony\Component\Form\FormFactory;

class BooleanFilter extends Filter
{
    public function filter($queryBuilder, $alias, $field, $value)
    {
        if ($this->getField()->getAttribute('multiple')) {

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

            $queryBuilder->andWhere(sprintf('%s.%s = :%s',
                $alias,
                $field,
                $this->getName()
            ));

            $queryBuilder->setParameter($this->getName(), $value == 'true' ? 1 : 0);
        }
    }

    public function defineFieldBuilder(FormFactory $formFactory)
    {
        $options = array(
            'choices' => array(
                'all'   => 'all',
                'true'  => 'true',
                'false' => 'false'
            ),
            'required' => true
        );

        $options = array_merge($options, $this->getFieldDescription()->getOption('filter_field_options', array()));

        $this->field = $formFactory->createNamedBuilder('choice', $this->getName(), null, $options)->getForm();
    }
}