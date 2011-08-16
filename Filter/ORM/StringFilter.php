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

use Symfony\Component\Form\FormFactory;
use Doctrine\ORM\QueryBuilder;

class StringFilter extends Filter
{

    /**
     * @param Querybuilder $queryBuilder
     * @param string $alias
     * @param string $field
     * @param mixed $value
     * @return
     */
    public function filter($queryBuilder, $alias, $field, $value)
    {
        if ($value == null) {
            return;
        }

        $value  = sprintf($this->getOption('format'), $value);

        // c.name LIKE '%word%' => c.name LIKE :fieldName
        $queryBuilder->andWhere(sprintf('%s.%s LIKE :%s',
            $alias,
            $field,
            $this->getName()
        ));

        $queryBuilder->setParameter($this->getName(), $value);
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return array(
            'format'   => '%%%s%%'
        );
    }

    /**
     * @param \Symfony\Component\Form\FormFactory $formFactory
     * @return void
     */
    public function defineFieldBuilder(FormFactory $formFactory)
    {
        $options = $this->fieldDescription->getOption('filter_field_options', array('required' => false));

        $this->field = $formFactory->createNamedBuilder('text', $this->getName(), null, $options)->getForm();
    }
}