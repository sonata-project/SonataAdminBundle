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

use Sonata\AdminBundle\Extension\Event\EventInterface;
use Sonata\AdminBundle\Extension\Event\MessageInterface;
use Sonata\AdminBundle\Extension\Event\TaskInterface;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ExtensionEmitter
{
    /**
     * @var ExtensionNotifier
     */
    private $notifier;

    /**
     * @var ExtensionProcessor
     */
    private $processor;

    public function __construct(ExtensionNotifier $notifier, ExtensionProcessor $processor)
    {
        $this->notifier = $notifier;
        $this->processor = $processor;
    }

    /**
     * @return TaskInterface|null
     */
    public function dispatch(EventInterface $event)
    {
        if ($event instanceof TaskInterface) {
            return $this->processor->process($event);
        }

        if ($event instanceof MessageInterface) {
            $this->notifier->notify($event);
        }
    }
}
