<?php

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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Sonata menu builder.
 *
 * @author Martin Hasoň <martin.hason@gmail.com>
 * @author Alexandru Furculita <alex@furculita.net>
 */
class MenuBuilder
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

    /**
     * @param Pool                     $pool
     * @param FactoryInterface         $factory
     * @param MenuProviderInterface    $provider
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Pool $pool, FactoryInterface $factory, MenuProviderInterface $provider, EventDispatcherInterface $eventDispatcher)
    {
        $this->pool = $pool;
        $this->factory = $factory;
        $this->provider = $provider;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Builds sidebar menu.
     *
     * @return ItemInterface
     */
    public function createSidebarMenu()
    {
        $menu = $this->factory->createItem('root');

        foreach ($this->pool->getAdminGroups() as $name => $group) {
            $extras = array(
                'icon'            => $group['icon'],
                'label_catalogue' => $group['label_catalogue'],
                'roles'           => $group['roles'],
            );

            $menuProvider = isset($group['provider']) ? $group['provider'] : 'sonata_group_menu';
            $subMenu = $this->provider->get(
                $menuProvider,
                array(
                    'name'  => $name,
                    'group' => $group,
                )
            );

            $subMenu = $menu->addChild($subMenu);
            $subMenu->setExtras(array_merge($subMenu->getExtras(), $extras));
        }

        $event = new ConfigureMenuEvent($this->factory, $menu);
        $this->eventDispatcher->dispatch(ConfigureMenuEvent::SIDEBAR, $event);

        return $event->getMenu();
    }
}
