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

class CallbackFilter extends Filter
{
    protected function association($queryBuilder, $value)
    {
        return array($queryBuilder->getRootAlias(), false);
    }

    public function filter($queryBuilder, $alias, $field, $value)
    {
        if (!is_callable($this->getOption('filter'))) {
            throw new \RuntimeException('Please provide a valid callback option "filter" for field "' . $this->getName() . "'");
        }

        call_user_func($this->getOption('filter'), $queryBuilder, $alias, $field, $value);
    }

    /**
     *    $this->filter_fields['custom'] = array(
     *        'type'           => 'callback',
     *        'filter_options' => array(
     *           'filter'  => array($this, 'getCustomFilter'),
     *           'type'    => 'type_name'
     *       )
     *    );
     *
     * @return void
     */
    public function getDefaultOptions()
    {
        return array(
            'filter' => null,
            'type'   => 'text',
        );
    }

    public function defineFieldBuilder(FormFactory $formFactory)
    {
        $options = $this->getFieldDescription()->getOption('filter_field_options', array());

        $this->field = $formFactory->createNamedBuilder($this->getOption('type'), $this->getName(), null, $options)->getForm();
    }
}