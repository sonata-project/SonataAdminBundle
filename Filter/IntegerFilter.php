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

class IntegerFilter extends Filter
{

   public function filter(QueryBuilder $queryBuilder, $alias, $field, $value)
    {

        if ($value == null) {
            return;
        }

        $value      = sprintf($this->getOption('format'), $value);

        // c.name > '1' => c.name OPERATOR :FIELDNAME
        $queryBuilder->andWhere(sprintf('%s.%s %s :%s',
            $alias,
            $field,
            $this->getOption('operator'),
            $this->getName()
        ));

        $queryBuilder->setParameter($this->getName(), $value);
    }

    protected function configure()
    {
        $this->addOption('operator', '=');
        $this->addOption('format', '%d');

        parent::configure();
    }

   public function getFormField()
   {
       return new \Symfony\Component\Form\TextField(
           $this->getName(),
           $this->description->getOption('filter_field_options', array('required' => false))
       );
   }
}