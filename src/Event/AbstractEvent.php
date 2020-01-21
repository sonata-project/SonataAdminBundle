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

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;
use Symfony\Contracts\EventDispatcher\Event as ContractsEvent;

if (class_exists(LegacyEventDispatcherProxy::class)) {
    /**
     * @internal
     */
    abstract class AbstractEvent extends ContractsEvent
    {
    }
} else {
    /**
     * @internal
     */
    abstract class AbstractEvent extends Event
    {
    }
}
