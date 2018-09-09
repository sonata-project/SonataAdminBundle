<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Extension\Event;

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ConfigureTabMenuMessage implements MessageInterface
{
    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var MenuItemInterface
     */
    private $menu;

    /**
     * @var string
     */
    private $action;

    /**
     * @var AdminInterface
     */
    private $childAdmin;

    /**
     * @param string $action
     */
    public function __construct(
        AdminInterface $admin,
        MenuItemInterface $menu,
        $action,
        AdminInterface $childAdmin = null
    ) {
        $this->admin = $admin;
        $this->menu = $menu;
        $this->action = $action;
        $this->childAdmin = $childAdmin;
    }

    /**
     * @return AdminInterface
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @return MenuItemInterface
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return AdminInterface
     */
    public function getChildAdmin()
    {
        return $this->childAdmin;
    }
}
