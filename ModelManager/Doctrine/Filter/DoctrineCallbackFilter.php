<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\ModelManager\Doctrine\Filter;

use Sonata\AdminBundle\Admin\FieldDescription;
use Doctrine\ORM\QueryBuilder;

class DoctrineCallbackFilter extends DoctrineFilter
{

    protected function association($queryBuilder, $value)
    {
        return array($queryBuilder->getRootAlias(), false);
    }

    public function filter($queryBuilder, $alias, $field, $value)
    {

        call_user_func($this->getOption('filter'), $queryBuilder, $alias, $field, $value);
    }

    /**
     *    $this->filter_fields['custom'] = array(
     *        'type'           => 'callback',
     *        'filter_options' => array(
     *           'filter'  => array($this, 'getCustomFilter'),
     *           'field'   => array($this, 'getCustomField')
     *       )
     *    );
     *
     * @return void
     */
    protected function configure()
    {

        $this->addRequiredOption('filter');
        $this->addRequiredOption('field');

        parent::configure();
    }

    public function getFormField()
    {

        return call_user_func($this->getOption('field'), $this);
    }
}
