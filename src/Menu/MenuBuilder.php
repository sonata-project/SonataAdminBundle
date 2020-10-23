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

namespace Sonata\AdminBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Event\ConfigureMenuEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Sonata menu builder.
 *
 * @author Martin Hasoň <martin.hason@gmail.com>
 * @author Alexandru Furculita <alex@furculita.net>
 */
final class MenuBuilder
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var MenuProviderInterface
     */
    private $provider;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        Pool $pool,
        FactoryInterface $factory,
        MenuProviderInterface $provider,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->pool = $pool;
        $this->factory = $factory;
        $this->provider = $provider;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Builds sidebar menu.
     */
    public function createSidebarMenu(): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        foreach ($this->pool->getAdminGroups() as $name => $group) {
            $extras = [
                'icon' => $group['icon'],
                'label_catalogue' => $group['label_catalogue'],
                'roles' => $group['roles'],
                'sonata_admin' => true,
            ];

            $menuProvider = $group['provider'] ?? 'sonata_group_menu';
            $subMenu = $this->provider->get(
                $menuProvider,
                [
                    'name' => $name,
                    'group' => $group,
                ]
            );

            $subMenu = $menu->addChild($subMenu);
            $subMenu->setExtras(array_merge($subMenu->getExtras(), $extras));
        }

        $event = new ConfigureMenuEvent($this->factory, $menu);
        $this->eventDispatcher->dispatch($event, ConfigureMenuEvent::SIDEBAR);

        return $event->getMenu();
    }
}
