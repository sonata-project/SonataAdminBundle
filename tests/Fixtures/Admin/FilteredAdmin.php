<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;

class FilteredAdmin extends AbstractAdmin
{
    protected function configureDefaultFilterValues(array &$filterValues): void
    {
        $filterValues['foo'] = [
            'type' => '1',
            'value' => 'bar',
        ];
        $filterValues['baz'] = [
            'type' => '2',
            'value' => 'test',
        ];
    }
}
