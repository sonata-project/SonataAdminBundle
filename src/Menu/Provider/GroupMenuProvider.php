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
 *
 * @phpstan-import-type Item from \Sonata\AdminBundle\Admin\Pool
 * @phpstan-import-type Group from \Sonata\AdminBundle\Admin\Pool
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
     * @param array<string, mixed> $options
     *
     * @throws \InvalidArgumentException if the menu does not exists
     */
    public function get(string $name, array $options = []): ItemInterface
    {
        if (!isset($options['name'])) {
            throw new \InvalidArgumentException('The option "name" is required.');
        }
        $menuItem = $this->menuFactory->createItem($options['name']);

        if (!isset($options['group'])) {
            throw new \InvalidArgumentException('The option "group" is required.');
        }
        /** @phpstan-var Group $group */
        $group = $options['group'];

        if (false === $group['on_top']) {
            foreach ($group['items'] as $item) {
                if ($this->canGenerateMenuItem($item, $group)) {
                    $menuItem->addChild($this->generateMenuItem($item, $group));
                }
            }

            if (false === $menuItem->hasChildren()) {
                $menuItem->setDisplay(false);
            } elseif ($group['keep_open']) {
                $menuItem->setAttribute('class', 'keep-open');
                $menuItem->setExtra('keep_open', $group['keep_open']);
            }
        } elseif (
            1 === \count($group['items'])
            && $this->canGenerateMenuItem($group['items'][0], $group)
        ) {
            $menuItem = $this->generateMenuItem($group['items'][0], $group);
            $menuItem->setExtra('on_top', $group['on_top']);
        } else {
            $menuItem->setDisplay(false);
        }

        $menuItem->setLabel($group['label']);

        return $menuItem;
    }

    /**
     * Checks whether a menu exists in this provider.
     *
     * @param mixed[] $options
     */
    public function has(string $name, array $options = []): bool
    {
        return 'sonata_group_menu' === $name;
    }

    /**
     * @phpstan-param Item $item
     * @phpstan-param Group $group
     */
    private function canGenerateMenuItem(array $item, array $group): bool
    {
        if (isset($item['admin']) && '' !== $item['admin']) {
            $admin = $this->pool->getInstance($item['admin']);

            // skip menu item if no `list` url is available or user doesn't have the LIST access rights
            return $admin->hasRoute('list') && $admin->hasAccess('list');
        }

        // Making the checker behave affirmatively even if it's globally unanimous
        // Still must be granted unanimously to group and item

        $isItemGranted = true;
        if (isset($item['roles']) && [] !== $item['roles']) {
            $isItemGranted = false;
            foreach ($item['roles'] as $role) {
                if ($this->checker->isGranted($role)) {
                    $isItemGranted = true;
                    break;
                }
            }
        }

        $isGroupGranted = true;
        if (isset($group['roles']) && [] !== $group['roles']) {
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

    /**
     * @phpstan-param Item $item
     * @phpstan-param Group $group
     */
    private function generateMenuItem(array $item, array $group): ItemInterface
    {
        if (isset($item['admin']) && '' !== $item['admin']) {
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

            return $this->menuFactory->createItem($admin->getLabel() ?? '', $options);
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
