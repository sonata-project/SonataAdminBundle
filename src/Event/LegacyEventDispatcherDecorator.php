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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;

/**
 * This class check the legacy signature of EventDispatcherInterface::dispatch() and decorate it if needed.
 *
 * BC for Symfony < 4.3 where `dispatch()` has a different signature
 * NEXT_MAJOR: Remove this class
 *
 * @author Denis Golubovskiy <bukashk0zzz@gmail.com>
 */
final class LegacyEventDispatcherDecorator
{
    /**
     * @param ContractsEventDispatcherInterface|EventDispatcherInterface|null $dispatcher
     *
     * @return ContractsEventDispatcherInterface|EventDispatcherInterface|null
     */
    public static function decorate($dispatcher)
    {
        if (null === $dispatcher) {
            return null;
        }
        $r = new \ReflectionMethod($dispatcher, 'dispatch');
        $param2 = $r->getParameters()[1] ?? null;

        if (!$param2 || !$param2->hasType() || $param2->getType()->isBuiltin()) {
            return $dispatcher;
        }

        @trigger_error(sprintf('The signature of the "%s::dispatch()" method should be updated to "dispatch($event, string $eventName = null)", not doing so is deprecated since Symfony 4.3.', $r->class), E_USER_DEPRECATED);

        $legacyEventDispatcherProxy = new LegacyEventDispatcherProxy();
        $legacyEventDispatcherProxy->dispatcher = $dispatcher;

        return $legacyEventDispatcherProxy;
    }
}
