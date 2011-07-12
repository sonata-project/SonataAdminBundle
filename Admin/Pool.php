<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Pool
{
    protected $container = null;

    protected $adminServiceIds = array();

    protected $adminGroups = array();

    protected $adminClasses = array();

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getGroups()
    {
        $groups = $this->adminGroups;

        foreach ($this->adminGroups as $name => $adminGroup) {
            foreach ($adminGroup as $id => $options) {
                $groups[$name][$id] = $this->getInstance($id);
            }
        }

        return $groups;
    }

    public function getDashboardGroups()
    {
        $groups = $this->adminGroups;

        foreach ($this->adminGroups as $name => $adminGroup) {

            foreach ($adminGroup as $id => $options) {

                if (!$options['show_in_dashboard']) {
                    unset($groups[$name][$id]);
                    continue;

                }

                $groups[$name][$id] = $this->container->get($id);
            }

            if (empty($groups[$name])) {
                unset($groups[$name]);
            }
        }

        return $groups;
    }

    /**
     * return the admin related to the given $class
     *
     * @param string $class
     * @return \Sonata\AdminBundle\Admin\AdminInterface|null
     */
    public function getAdminByClass($class)
    {
        if (!isset($this->adminClasses[$class])) {
            return null;
        }

        return $this->getInstance($this->adminClasses[$class]);
    }

    /**
     * Returns an admin class by its Admin code
     * ie : sonata.news.admin.post|sonata.news.admin.comment => return the child class of post
     *
     * @param string $adminCode
     * @return \Sonata\AdminBundle\Admin\AdminInterface|null
     */
    public function getAdminByAdminCode($adminCode)
    {
        $codes = explode('|', $adminCode);
        $admin = false;
        foreach ($codes as $code) {
            if ($admin == false) {
                $admin = $this->getInstance($code);
            } else if ($admin->hasChild($code)) {
                $admin = $admin->getChild($code);
            }
        }

        return $admin;
    }

    /**
     * Returns a new admin instance depends on the given code
     *
     * @param $id
     * @return \Sonata\AdminBundle\Admin\AdminInterface
     */
    public function getInstance($id)
    {
        return $this->container->get($id);
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function setAdminGroups($adminGroups)
    {
        $this->adminGroups = $adminGroups;
    }

    public function getAdminGroups()
    {
        return $this->adminGroups;
    }

    public function setAdminServiceIds($adminServiceIds)
    {
        $this->adminServiceIds = $adminServiceIds;
    }

    public function getAdminServiceIds()
    {
        return $this->adminServiceIds;
    }

    public function setAdminClasses($adminClasses)
    {
        $this->adminClasses = $adminClasses;
    }

    public function getAdminClasses()
    {
        return $this->adminClasses;
    }
}