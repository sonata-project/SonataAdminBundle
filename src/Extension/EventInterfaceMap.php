<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Extension;

use InvalidArgumentException;
use Sonata\AdminBundle\Admin\Extension;
use function array_key_exists;
use function get_class;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class EventInterfaceMap
{
    const EVENT_INTERFACE_MAP = [
        Event\AlterNewInstanceMessage::class => Extension\AlterNewInstanceInterface::class,
        Event\AlterObjectMessage::class => Extension\AlterObjectInterface::class,
        Event\ConfigureActionButtonsTask::class => Extension\ConfigureActionButtonsInterface::class,
        Event\ConfigureBatchActionsTask::class => Extension\ConfigureBatchActionsInterface::class,
        Event\ConfigureDatagridFiltersMessage::class => Extension\ConfigureDatagridFieldsInterface::class,
        Event\ConfigureDefaultFilterValuesMessage::class => Extension\ConfigureDefaultFilterValuesInterface::class,
        Event\ConfigureExportFieldsTask::class => Extension\ConfigureExportFieldsInterface::class,
        Event\ConfigureFormFieldsMessage::class => Extension\ConfigureFormFieldsInterface::class,
        Event\ConfigureListFieldsMessage::class => Extension\ConfigureListFieldsInterface::class,
        Event\ConfigureQueryMessage::class => Extension\ConfigureQueryInterface::class,
        Event\ConfigureRoutesMessage::class => Extension\ConfigureRoutesInterface::class,
        Event\ConfigureShowFieldsMessage::class => Extension\ConfigureShowFieldsInterface::class,
        Event\ConfigureTabMenuMessage::class => Extension\ConfigureTabMenuInterface::class,
        Event\GetAccessMappingTask::class => Extension\GetAccessMappingInterface::class,
        Event\GetPersistentParametersTask::class => Extension\GetPersistentParametersInterface::class,
        Event\PostPersistMessage::class => Extension\PostPersistInterface::class,
        Event\PostRemoveMessage::class => Extension\PostRemoveInterface::class,
        Event\PostUpdateMessage::class => Extension\PostUpdateInterface::class,
        Event\PrePersistMessage::class => Extension\PrePersistInterface::class,
        Event\PreRemoveMessage::class => Extension\PreRemoveInterface::class,
        Event\PreUpdateMessage::class => Extension\PreUpdateInterface::class,
        Event\ValidateMessage::class => Extension\ValidateInterface::class,
    ];

    /**
     * @return string
     */
    public static function get(Event\EventInterface $event)
    {
        $eventClass = get_class($event);

        if (!self::has($event)) {
            throw new InvalidArgumentException(sprintf(
                "Interface for event '%s' couldn't be found",
                $eventClass
            ));
        }

        return self::EVENT_INTERFACE_MAP[$eventClass];
    }

    /**
     * @return bool
     */
    public static function has(Event\EventInterface $event)
    {
        return array_key_exists(get_class($event), self::EVENT_INTERFACE_MAP);
    }
}
