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

namespace Sonata\AdminBundle\Menu\Provider;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Menu provider based on group options.
 *
 * @author Alexandru Furculita <alex@furculita.net>
 */
final class GroupMenuProvider implements MenuProviderInterface
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
     * @var AuthorizationCheckerInterface
     */
    private $checker;

    public function __construct(FactoryInterface $menuFactory, Pool $pool, AuthorizationCheckerInterface $checker)
    {
        $this->menuFactory = $menuFactory;
        $this->pool = $pool;
        $this->checker = $checker;
    }

    /**
     * Retrieves the menu based on the group options.
     *
     * @throws \InvalidArgumentException if the menu does not exists
     */
    public function get(string $name, array $options = []): ItemInterface
    {
        /**
         * @var array{ label: string, label_catalogue: string, icon: string, on_top?: bool, keep_open: bool, provider: string, items: list }
         */
        $group = $options['group'];

        $menuItem = $this->menuFactory->createItem($options['name']);

        if (!\array_key_exists('on_top', $group) || false === $group['on_top']) {
            foreach ($group['items'] as $item) {
                if ($this->canGenerateMenuItem($item, $group)) {
                    $menuItem->addChild($this->generateMenuItem($item, $group));
                }
            }

            if (false === $menuItem->hasChildren()) {
                $menuItem->setDisplay(false);
            } elseif (!empty($group['keep_open'])) {
                $menuItem->setAttribute('class', 'keep-open');
                $menuItem->setExtra('keep_open', $group['keep_open']);
            }
        } elseif (1 === \count($group['items'])) {
            if ($this->canGenerateMenuItem($group['items'][0], $group)) {
                $menuItem = $this->generateMenuItem($group['items'][0], $group);
                $menuItem->setExtra('on_top', $group['on_top']);
            } else {
                $menuItem->setDisplay(false);
            }
        }
        $menuItem->setLabel($group['label']);

        return $menuItem;
    }

    /**
     * Checks whether a menu exists in this provider.
     */
    public function has(string $name, array $options = []): bool
    {
        return 'sonata_group_menu' === $name;
    }

    private function canGenerateMenuItem(array $item, array $group): bool
    {
        if (isset($item['admin']) && !empty($item['admin'])) {
            $admin = $this->pool->getInstance($item['admin']);

            // skip menu item if no `list` url is available or user doesn't have the LIST access rights
            return $admin->hasRoute('list') && $admin->hasAccess('list');
        }

        // Making the checker behave affirmatively even if it's globally unanimous
        // Still must be granted unanimously to group and item

        $isItemGranted = true;
        if (!empty($item['roles'])) {
            $isItemGranted = false;
            foreach ($item['roles'] as $role) {
                if ($this->checker->isGranted($role)) {
                    $isItemGranted = true;
                    break;
                }
            }
        }

        $isGroupGranted = true;
        if (!empty($group['roles'])) {
            $isGroupGranted = false;
            foreach ($group['roles'] as $role) {
                if ($this->checker->isGranted($role)) {
                    $isGroupGranted = true;
                    break;
                }
            }
        }

        return $isItemGranted && $isGroupGranted;
    }

    private function generateMenuItem(array $item, array $group): ItemInterface
    {
        if (isset($item['admin']) && !empty($item['admin'])) {
            $admin = $this->pool->getInstance($item['admin']);

            $options = $admin->generateMenuUrl(
                'list',
                [],
                $item['route_absolute'] ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH
            );
            $options['extras'] = [
                'label_catalogue' => $admin->getTranslationDomain(),
                'admin' => $admin,
            ];

            return $this->menuFactory->createItem($admin->getLabel(), $options);
        }

        return $this->menuFactory->createItem($item['label'], [
            'route' => $item['route'],
            'routeParameters' => $item['route_params'],
            'routeAbsolute' => $item['route_absolute'],
            'extras' => [
                'label_catalogue' => $group['label_catalogue'],
            ],
        ]);
    }
}
