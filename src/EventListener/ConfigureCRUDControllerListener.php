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

namespace Sonata\AdminBundle\EventListener;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 */
final class ConfigureCRUDControllerListener implements EventSubscriberInterface
{
    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (\is_array($controller)) {
            $controller = $controller[0];
        }

        if (!$controller instanceof CRUDController) {
            return;
        }

        $request = $event->getRequest();

        $controller->configureAdmin($request);
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => 'onKernelController'];
    }
}
