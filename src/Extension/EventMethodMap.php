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
use function array_key_exists;
use function get_class;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class EventMethodMap
{
    const EVENT_METHOD_MAP = [
        Event\AlterNewInstanceMessage::class => 'alterNewInstance',
        Event\AlterObjectMessage::class => 'alterObject',
        Event\ConfigureActionButtonsTask::class => 'configureActionButtons',
        Event\ConfigureBatchActionsTask::class => 'configureBatchActions',
        Event\ConfigureDatagridFiltersMessage::class => 'configureDatagridFilters',
        Event\ConfigureDefaultFilterValuesMessage::class => 'configureDefaultFilterValues',
        Event\ConfigureExportFieldsTask::class => 'configureExportFields',
        Event\ConfigureFormFieldsMessage::class => 'configureFormFields',
        Event\ConfigureListFieldsMessage::class => 'configureListFields',
        Event\ConfigureQueryMessage::class => 'configureQuery',
        Event\ConfigureRoutesMessage::class => 'configureRoutes',
        Event\ConfigureShowFieldsMessage::class => 'configureShowFields',
        Event\ConfigureTabMenuMessage::class => 'configureTabMenu',
        Event\GetAccessMappingTask::class => 'getAccessMapping',
        Event\GetPersistentParametersTask::class => 'getPersistentParameters',
        Event\PostPersistMessage::class => 'postPersist',
        Event\PostRemoveMessage::class => 'postRemove',
        Event\PostUpdateMessage::class => 'postUpdate',
        Event\PrePersistMessage::class => 'prePersist',
        Event\PreRemoveMessage::class => 'preRemove',
        Event\PreUpdateMessage::class => 'preUpdate',
        Event\ValidateMessage::class => 'validate',
    ];

    /**
     * @return string
     */
    public static function get(Event\EventInterface $event)
    {
        $eventClass = get_class($event);

        if (!self::has($event)) {
            throw new InvalidArgumentException(sprintf(
                "Method for event '%s' couldn't be found",
                $eventClass
            ));
        }

        return self::EVENT_METHOD_MAP[$eventClass];
    }

    /**
     * @return bool
     */
    public static function has(Event\EventInterface $event)
    {
        return array_key_exists(get_class($event), self::EVENT_METHOD_MAP);
    }
}
