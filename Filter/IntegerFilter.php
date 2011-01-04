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


class IntegerFilter extends Filter
{

    public function filter($query_builder, $alias, $field, $value)
    {

        if($value == null) {
            return;
        }

        $value      = sprintf($this->getOption('format'), $value);

        // c.name > '1' => c.name OPERATOR :FIELDNAME
        $query_builder->andWhere(sprintf('%s.%s %s :%s',
            $alias,
            $field,
            $this->getOption('operator'),
            $this->getName()
        ));

        $query_builder->setParameter($this->getName(), $value);
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
           $this->description['filter_field_options']
       );
   }
}