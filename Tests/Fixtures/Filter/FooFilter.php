<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Filter;

use Sonata\AdminBundle\Filter\Filter;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class FooFilter extends Filter
{
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $value)
    {
    }

    public function apply($query, $value)
    {
    }

    public function getDefaultOptions()
    {
        return array(
            'foo' => 'bar',
        );
    }

    public function getRenderSettings()
    {
    }
}
