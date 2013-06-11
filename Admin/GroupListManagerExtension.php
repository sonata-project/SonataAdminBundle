<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sonata\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;

class GroupListManagerExtension extends AdminExtension
{
    /** @var array $groups */
    protected $groups;

    /**
     * @param array $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param \Sonata\AdminBundle\Route\RouteCollection $collection
     */
    public function configureRoutes(AdminInterface $admin, RouteCollection $collection)
    {
        $facade = $this->groups[$admin->getClass()];
        /** @var RouteCollection $routes */
        $routes = $facade->getRoutes();

        $rc = new RouteCollection($collection->getBaseCodeRoute(), $routes->getBaseRouteName(), $routes->getBaseRoutePattern(), $routes->getBaseControllerName());
        $rc->add('list', '/list');

        $collection->remove('list');
        $collection->addCollection($rc);
    }
}
