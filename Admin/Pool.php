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

    protected $templates    = array();

    protected $title;

    protected $titleLogo;

    protected $options;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param string                                                    $title
     * @param string                                                    $logoTitle
     * @param array                                                     $options
     */
    public function __construct(ContainerInterface $container, $title, $logoTitle, $options = array())
    {
        $this->container = $container;
        $this->title     = $title;
        $this->titleLogo = $logoTitle;
        $this->options   = $options;
    }

    /**
     * @return array
     */
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

    /**
     * Returns whether an admin group exists or not.
     *
     * @param string $group
     * @return bool
     */
    public function hasGroup($group)
    {
        return isset($this->adminGroups[$group]);
    }

    /**
     * @return array
     */
    public function getDashboardGroups()
    {
        $groups = $this->adminGroups;

        foreach ($this->adminGroups as $name => $adminGroup) {
            if (isset($adminGroup['items'])) {
                foreach ($adminGroup['items'] as $key => $id) {
                    $admin = $this->getInstance($id);

                    if ($admin->showIn(Admin::CONTEXT_DASHBOARD)) {
                        $groups[$name]['items'][$key] = $admin;
                    } else {
                        unset($groups[$name]['items'][$key]);
                    }
                }
            }

            if (empty($groups[$name]['items'])) {
                unset($groups[$name]);
            }
        }

        return $groups;
    }

    /**
     * Returns all admins related to the given $group
     *
     * @param string $group
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getAdminsByGroup($group)
    {
        if (!isset($this->adminGroups[$group])) {
            throw new \InvalidArgumentException(sprintf('Group "%s" not found in admin pool.', $group));
        }

        $admins = array();

        if (!isset($this->adminGroups[$group]['items'])) {
            return $admins;
        }

        foreach ($this->adminGroups[$group]['items'] as $id) {
            $admins[] = $this->getInstance($id);
        }

        return $admins;
    }

    /**
     * return the admin related to the given $class
     *
     * @param string $class
     *
     * @return \Sonata\AdminBundle\Admin\AdminInterface|null
     */
    public function getAdminByClass($class)
    {
        if (!$this->hasAdminByClass($class)) {
            return null;
        }

        return $this->getInstance($this->adminClasses[$class]);
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function hasAdminByClass($class)
    {
        return isset($this->adminClasses[$class]);
    }

    /**
     * Returns an admin class by its Admin code
     * ie : sonata.news.admin.post|sonata.news.admin.comment => return the child class of post
     *
     * @param string $adminCode
     *
     * @return \Sonata\AdminBundle\Admin\AdminInterface|null
     */
    public function getAdminByAdminCode($adminCode)
    {
        $codes = explode('|', $adminCode);
        $admin = false;
        foreach ($codes as $code) {
            if ($admin == false) {
                $admin = $this->getInstance($code);
            } elseif ($admin->hasChild($code)) {
                $admin = $admin->getChild($code);
            }
        }

        return $admin;
    }

    /**
     * Returns a new admin instance depends on the given code
     *
     * @param string $id
     *
     * @return \Sonata\AdminBundle\Admin\AdminInterface
     */
    public function getInstance($id)
    {
        return $this->container->get($id);
    }

    /**
     * @return null|\Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param array $adminGroups
     *
     * @return void
     */
    public function setAdminGroups(array $adminGroups)
    {
        $this->adminGroups = $adminGroups;
    }

    /**
     * @return array
     */
    public function getAdminGroups()
    {
        return $this->adminGroups;
    }

    /**
     * @param array $adminServiceIds
     *
     * @return void
     */
    public function setAdminServiceIds(array $adminServiceIds)
    {
        $this->adminServiceIds = $adminServiceIds;
    }

    /**
     * @return array
     */
    public function getAdminServiceIds()
    {
        return $this->adminServiceIds;
    }

    /**
     * @param array $adminClasses
     *
     * @return void
     */
    public function setAdminClasses(array $adminClasses)
    {
        $this->adminClasses = $adminClasses;
    }

    /**
     * @return array
     */
    public function getAdminClasses()
    {
        return $this->adminClasses;
    }

    /**
     * @param array $templates
     *
     * @return void
     */
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @param string $name
     *
     * @return null|string
     */
    public function getTemplate($name)
    {
        if (isset($this->templates[$name])) {
            return $this->templates[$name];
        }

        return null;
    }

    /**
     * @return string
     */
    public function getTitleLogo()
    {
        return $this->titleLogo;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getOption($name)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return null;
    }
}
