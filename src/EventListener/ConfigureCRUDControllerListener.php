<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\EventListener;

use SensioLabs\AdminBundle\Controller\CRUDController;
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
        } else {
            try {
                $reflection = new \ReflectionFunction($controller(...));
                $controller = $reflection->getClosureThis();
            } catch (\ReflectionException) {
                return;
            }
        }

        if (!$controller instanceof CRUDController) {
            return;
        }

        $request = $event->getRequest();

        $controller->configureAdmin($request);

        $controller->setTwigGlobals($request);
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => 'onKernelController'];
    }
}
