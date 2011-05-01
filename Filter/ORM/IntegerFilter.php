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

class IntegerFilter extends Filter
{
    public function filter($queryBuilder, $alias, $field, $value)
    {
        if ($value == null) {
            return;
        }

        // c.name > '1' => c.name OPERATOR :FIELDNAME
        $queryBuilder->andWhere(sprintf('%s.%s %s :%s',
            $alias,
            $field,
            $this->getOption('operator'),
            $this->getName()
        ));

        $queryBuilder->setParameter($this->getName(), (int)sprintf($this->getOption('format'), $value));
    }

    public function getDefaultOptions()
    {
        return array(
            'operator' => '=',
            'format'   => '%d'
        );
    }

   public function defineFieldBuilder(FormFactory $formFactory)
   {
       $options = $this->fieldDescription->getOption('filter_field_options', array('required' => false));

       $this->field = $formFactory->createNamedBuilder('text', $this->getName(), null, $options)->getForm();
   }
}