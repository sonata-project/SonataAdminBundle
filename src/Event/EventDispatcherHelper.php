<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;

/**
 * @internal
 */
final class EventDispatcherHelper
{
    public static function dispatch(EventDispatcherInterface $eventDispatcher, $event, string $eventName)
    {
        if (class_exists(LegacyEventDispatcherProxy::class)) {
            // Symfony 4.3+
            LegacyEventDispatcherProxy::decorate($eventDispatcher)->dispatch($event, $eventName);
        } else {
            $eventDispatcher->dispatch($eventName, $event);
        }
    }
}
