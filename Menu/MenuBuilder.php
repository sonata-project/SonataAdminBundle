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
use Symfony\Component\HttpFoundation\Request;

/**
 * Sonata menu builder.
 *
 * @author Martin Hasoň <martin.hason@gmail.com>
 */
class MenuBuilder
{
    private $pool;
    private $factory;
    private $provider;
    private $request;
    private $eventDispatcher;

    /**
     * Constructor.
     *
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
        $menu = $this->factory->createItem('root', array(
            'extras' => array(
                'request' => $this->request,
            ),
        ));

        foreach ($this->pool->getAdminGroups() as $name => $group) {
            $attributes = array();

            $extras = array(
                'icon'            => $group['icon'],
                'label_catalogue' => $group['label_catalogue'],
                'roles'           => $group['roles'],
            );

            // Check if the menu group is built by a menu provider
            if (isset($group['provider'])) {
                $subMenu = $this->provider->get($group['provider']);

                $menu
                    ->addChild($subMenu)
                    ->setExtras(array_merge($subMenu->getExtras(), $extras))
                    ->setAttributes(array_merge($subMenu->getAttributes(), $attributes))
                ;

                continue;
            }

            // The menu group is built by config
            $menu->addChild($name, array(
                'label'      => $group['label'],
                'attributes' => $attributes,
                'extras'     => $extras,
            ));

            foreach ($group['items'] as $item) {
                if (isset($item['admin']) && !empty($item['admin'])) {
                    $admin = $this->pool->getInstance($item['admin']);

                    // skip menu item if no `list` url is available or user doesn't have the LIST access rights
                    if (!$admin->hasRoute('list') || !$admin->isGranted('LIST')) {
                        continue;
                    }

                    $label = $admin->getLabel();
                    $options = $admin->generateMenuUrl('list');
                    $options['extras'] = array(
                        'translation_domain' => $admin->getTranslationDomain(),
                        'admin'              => $admin,
                    );
                } else {
                    $label = $item['label'];
                    $options = array(
                        'route'           => $item['route'],
                        'routeParameters' => $item['route_params'],
                        'extras'          => array(
                            'translation_domain' => $group['label_catalogue'],
                        ),
                    );
                }

                $menu[$name]->addChild($label, $options);
            }

            if (0 === count($menu[$name]->getChildren())) {
                $menu->removeChild($name);
            }
        }

        $event = new ConfigureMenuEvent($this->factory, $menu);
        $this->eventDispatcher->dispatch(ConfigureMenuEvent::SIDEBAR, $event);

        return $event->getMenu();
    }

    /**
     * Sets the request the service.
     *
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }
}
