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

use Sonata\AdminBundle\Admin\FieldDescription;
use Doctrine\ORM\QueryBuilder;

class StringFilter extends Filter
{

    public function filter($queryBuilder, $alias, $field, $value)
    {

        if ($value == null) {
            return;
        }

        $value      = sprintf($this->getOption('format'), $value);

        // c.name LIKE '%word%' => c.name LIKE :fieldName
        $queryBuilder->andWhere(sprintf('%s.%s LIKE :%s',
            $alias,
            $field,
            $this->getName()
        ));

        $queryBuilder->setParameter($this->getName(), $value);
    }

    protected function configure()
    {
        $this->addOption('format', '%%%s%%');

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