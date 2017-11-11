<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;

class FilteredAdmin extends AbstractAdmin
{
    protected function configureDefaultFilterValues(array &$filterValues)
    {
        $filterValues['foo'] = array(
            'type' => '1',
            'value' => 'bar',
        );
        $filterValues['baz'] = array(
            'type' => '2',
            'value' => 'test',
        );
    }
}
