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

use Sonata\AdminBundle\Extension\Event\TaskInterface;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ExtensionProcessor
{
    /**
     * @var ExtensionProvider
     */
    private $provider;

    public function __construct(ExtensionProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return TaskInterface
     */
    public function process(TaskInterface $event)
    {
        foreach ($this->provider->getListenersForEvent($event) as $extension) {
            if (EventMethodMap::has($event)) {
                $extension->{EventMethodMap::get($event)}($event);
            }
        }

        return $event;
    }
}
