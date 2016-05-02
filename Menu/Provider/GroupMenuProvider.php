<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Menu\Provider;

use Knp\Menu\FactoryInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Sonata\AdminBundle\Admin\Pool;

/**
 * Menu provider based on group options.
 *
 * @author Alexandru Furculita <alex@furculita.net>
 */
class GroupMenuProvider implements MenuProviderInterface
{
    /**
     * @var FactoryInterface
     */
    private $menuFactory;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @param FactoryInterface $menuFactory
     * @param Pool             $pool
     */
    public function __construct(FactoryInterface $menuFactory, Pool $pool)
    {
        $this->menuFactory = $menuFactory;
        $this->pool = $pool;
    }

    /**
     * Retrieves the menu based on the group options.
     *
     * @param string $name
     * @param array  $options
     *
     * @return \Knp\Menu\ItemInterface
     *
     * @throws \InvalidArgumentException if the menu does not exists
     */
    public function get($name, array $options = array())
    {
        $group = $options['group'];

        $menuItem = $this->menuFactory->createItem(
            $options['name'],
            array(
                'label' => $group['label'],
            )
        );

        if (empty($group['on_top'])) {
            foreach ($group['items'] as $item) {
                if (isset($item['admin']) && !empty($item['admin'])) {
                    $admin = $this->pool->getInstance($item['admin']);

                    // skip menu item if no `list` url is available or user doesn't have the LIST access rights
                    if (!$admin->hasRoute('list') || !$admin->isGranted('LIST')) {
                        continue;
                    }

                    $label = $admin->getLabel();
                    $options = $admin->generateMenuUrl('list', array(), $item['route_absolute']);
                    $options['extras'] = array(
                        'translation_domain' => $admin->getTranslationDomain(),
                        'admin'              => $admin,
                    );
                } else {
                    $label = $item['label'];
                    $options = array(
                        'route'           => $item['route'],
                        'routeParameters' => $item['route_params'],
                        'routeAbsolute'   => $item['route_absolute'],
                        'extras'          => array(
                            'translation_domain' => $group['label_catalogue'],
                        ),
                    );
                }

                $menuItem->addChild($label, $options);
            }

            if (false === $menuItem->hasChildren()) {
                $menuItem->setDisplay(false);
            }
        } else {
            foreach ($group['items'] as $item) {
                if (isset($item['admin']) && !empty($item['admin'])) {
                    $admin = $this->pool->getInstance($item['admin']);

                    // skip menu item if no `list` url is available or user doesn't have the LIST access rights
                    if (!$admin->hasRoute('list') || !$admin->isGranted('LIST')) {
                        continue;
                    }

                    $options = $admin->generateUrl('list');
                    $menuItem->setExtra('route', $admin->getBaseRouteName().'_list');
                    $menuItem->setExtra('on_top', $group['on_top']);
                    $menuItem->setUri($options);
                } else {
                    $menuItem->setUri($item['route']);
                }
            }
        }

        return $menuItem;
    }

    /**
     * Checks whether a menu exists in this provider.
     *
     * @param string $name
     * @param array  $options
     *
     * @return bool
     */
    public function has($name, array $options = array())
    {
        return 'sonata_group_menu' === $name;
    }
}
