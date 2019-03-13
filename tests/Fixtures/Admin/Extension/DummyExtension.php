<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Admin\Extension;

use Sonata\AdminBundle\Admin\Extension\ConfigureActionButtonsInterface;
use Sonata\AdminBundle\Admin\Extension\ConfigureBatchActionsInterface;
use Sonata\AdminBundle\Admin\Extension\ConfigureDefaultFilterValuesInterface;
use Sonata\AdminBundle\Extension\Event\ConfigureActionButtonsTask;
use Sonata\AdminBundle\Extension\Event\ConfigureBatchActionsTask;
use Sonata\AdminBundle\Extension\Event\ConfigureDefaultFilterValuesMessage;

class DummyExtension implements ConfigureActionButtonsInterface, ConfigureDefaultFilterValuesInterface, ConfigureBatchActionsInterface
{
    public function configureActionButtons(ConfigureActionButtonsTask $event)
    {
    }

    public function configureDefaultFilterValues(ConfigureDefaultFilterValuesMessage $event)
    {
    }

    public function configureBatchActions(ConfigureBatchActionsTask $event)
    {
    }
}
