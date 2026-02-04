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

namespace SensioLabs\AdminBundle\Tests\App\EventListener;

use SensioLabs\AdminBundle\Event\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ConfigureMenu implements EventSubscriberInterface
{
    private int $counter = 0;

    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::SIDEBAR => 'configureMenu',
        ];
    }

    public function configureMenu(ConfigureMenuEvent $configureMenuEvent): void
    {
        $configureMenuEvent->getMenu()->addChild(\sprintf('Dynamic Menu %s', ++$this->counter))->setAttribute('class', 'dynamic-menu');
    }
}
