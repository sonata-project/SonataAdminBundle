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

use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event as ContractsEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;

/**
 * An helper class to provide BC/FC with the legacy signature of EventDispatcherInterface::dispatch().
 *
 * BC for Symfony < 4.3 where `dispatch()` has a different signature
 * NEXT_MAJOR: Remove this class
 *
 * @author Javier Spagnoletti <jspagnoletti@nubity.com>
 */
final class LegacyEventDispatcherProxy implements ContractsEventDispatcherInterface
{
    /**
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * Proxies all method calls to the original event dispatcher.
     */
    public function __call($method, $arguments)
    {
        return $this->dispatcher->{$method}(...$arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($event/*, string $eventName = null*/)
    {
        $eventName = 1 < \func_num_args() ? func_get_arg(1) : null;

        if (\is_object($event)) {
            $eventName = $eventName ?? \get_class($event);
        } else {
            @trigger_error(sprintf('Calling the "%s::dispatch()" method with the event name as first argument is deprecated since Symfony 4.3, pass it second and provide the event object first instead.', ContractsEventDispatcherInterface::class), E_USER_DEPRECATED);
            $swap = $event;
            $event = $eventName ?? new Event();
            $eventName = $swap;

            if (!$event instanceof Event && !$event instanceof ContractsEvent) {
                throw new \TypeError(sprintf('Argument 1 passed to "%s::dispatch()" must be an instance of %s, %s given.', ContractsEventDispatcherInterface::class, Event::class, \is_object($event) ? \get_class($event) : \gettype($event)));
            }
        }

        $listeners = $this->getListeners($eventName);
        $stoppable = $event instanceof Event || $event instanceof ContractsEvent || $event instanceof StoppableEventInterface;

        if (null === $listeners) {
            $this->dispatcher->dispatch($eventName, $event);

            return $event;
        }

        foreach ($listeners as $listener) {
            if ($stoppable && $event->isPropagationStopped()) {
                break;
            }
            $listener($event, $eventName, $this);
        }

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        return $this->dispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        return $this->dispatcher->addSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function removeListener($eventName, $listener)
    {
        return $this->dispatcher->removeListener($eventName, $listener);
    }

    /**
     * {@inheritdoc}
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        return $this->dispatcher->removeSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function getListeners($eventName = null)
    {
        return $this->dispatcher->getListeners($eventName);
    }

    /**
     * {@inheritdoc}
     */
    public function getListenerPriority($eventName, $listener)
    {
        return $this->dispatcher->getListenerPriority($eventName, $listener);
    }

    /**
     * {@inheritdoc}
     */
    public function hasListeners($eventName = null)
    {
        return $this->dispatcher->hasListeners($eventName);
    }
}
