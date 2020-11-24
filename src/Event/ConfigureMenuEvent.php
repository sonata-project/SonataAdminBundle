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

namespace Sonata\AdminBundle\Event;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Menu builder event. Used for extending the menus.
 *
 * @author Martin Hasoň <martin.hason@gmail.com>
 */
final class ConfigureMenuEvent extends Event
{
    public const SIDEBAR = 'sonata.admin.event.configure.menu.sidebar';

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var ItemInterface
     */
    private $menu;

    public function __construct(FactoryInterface $factory, ItemInterface $menu)
    {
        $this->factory = $factory;
        $this->menu = $menu;
    }

    public function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    public function getMenu(): ItemInterface
    {
        return $this->menu;
    }
}
