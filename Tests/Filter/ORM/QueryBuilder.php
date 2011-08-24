<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Filter\ORM;

use Sonata\AdminBundle\Filter\ORM\Filter;


class QueryBuilder
{
    public $parameters = array();

    public $query = array();

    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    public function andWhere($query)
    {
        $this->query[] = $query;
    }
}