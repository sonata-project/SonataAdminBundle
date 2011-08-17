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
use Symfony\Component\Translation\TranslatorInterface;
use Sonata\AdminBundle\Form\Type\Filter\BooleanType;

class BooleanFilter extends Filter
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
        if ($this->getField()->getAttribute('multiple')) {
            $values = array();
            foreach ($value as $v) {
                if (!in_array($v, array(BooleanType::TYPE_NO, BooleanType::TYPE_YES))) {
                   continue;
                }

                $values[] = $v ==  ((int)$value == BooleanType::TYPE_YES) ? 1 : 0;
            }

            if (count($values) == 0) {
                return;
            }

            $queryBuilder->andWhere($queryBuilder->expr()->in(sprintf('%s.%s', $alias, $field), $values));

        } else {

            if (!in_array($value, array(BooleanType::TYPE_NO, BooleanType::TYPE_YES))) {
                return;
            }

            $queryBuilder->andWhere(sprintf('%s.%s = :%s', $alias, $field, $this->getName()));
            $queryBuilder->setParameter($this->getName(), ((int)$value == BooleanType::TYPE_YES) ? 1 : 0);
        }
    }
}