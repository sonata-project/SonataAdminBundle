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

namespace Sonata\AdminBundle\Tests\App\EventListener;

use Sonata\AdminBundle\Event\ConfigureMenuEvent;
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
