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

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ConfigureRoutesMessage implements MessageInterface
{
    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var RouteCollection
     */
    private $routeCollection;

    public function __construct(AdminInterface $admin, RouteCollection $routeCollection)
    {
        $this->admin = $admin;
        $this->routeCollection = $routeCollection;
    }

    /**
     * @return AdminInterface
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @return RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->routeCollection;
    }
}
