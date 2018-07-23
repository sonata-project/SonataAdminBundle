<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Admin\Extension;

use Sonata\AdminBundle\Admin\Extension\AdminExtensionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

class DummyExtension implements AdminExtensionInterface
{
    public function configureBatchActions(AdminInterface $admin, array $actions)
    {
        return [];
    }

    public function configureActionButtons(AdminInterface $admin, $list, $action, $object)
    {
        return [];
    }

    public function configureDefaultFilterValues(AdminInterface $admin, array &$filterValues)
    {
        return [];
    }
}
