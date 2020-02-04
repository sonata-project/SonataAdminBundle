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

namespace Sonata\AdminBundle\Admin;

use InvalidArgumentException;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class Pool
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string[]
     */
    protected $adminServiceIds = [];

    /**
     * @var array
     */
    protected $adminGroups = [];

    /**
     * @var array
     */
    protected $adminClasses = [];

    /**
     * @deprecated since sonata-project/admin-bundle 3.34, will be dropped in 4.0. Use TemplateRegistry "sonata.admin.global_template_registry" instead
     *
     * @var array
     */
    protected $templates = [];

    /**
     * @var array
     */
    protected $assets = [];

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $titleLogo;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var MutableTemplateRegistryInterface
     */
    private $templateRegistry;

    /**
     * @param string $title
     * @param string $logoTitle
     * @param array  $options
     */
    public function __construct(
        ContainerInterface $container,
        $title,
        $logoTitle,
        $options = [],
        PropertyAccessorInterface $propertyAccessor = null
    ) {
        $this->container = $container;
        $this->title = $title;
        $this->titleLogo = $logoTitle;
        $this->options = $options;
        $this->propertyAccessor = $propertyAccessor;
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
     *
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
                foreach ($adminGroup['items'] as $key => $item) {
                    // Only Admin Group should be returned
                    if ('' !== $item['admin']) {
                        $admin = $this->getInstance($item['admin']);

                        if ($admin->showIn(AbstractAdmin::CONTEXT_DASHBOARD)) {
                            $groups[$name]['items'][$key] = $admin;
                        } else {
                            unset($groups[$name]['items'][$key]);
                        }
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
     * Returns all admins related to the given $group.
     *
     * @param string $group
     *
     * @throws \InvalidArgumentException
     *
     * @return AdminInterface[]
     */
    public function getAdminsByGroup($group)
    {
        if (!isset($this->adminGroups[$group])) {
            throw new \InvalidArgumentException(sprintf('Group "%s" not found in admin pool.', $group));
        }

        $admins = [];

        if (!isset($this->adminGroups[$group]['items'])) {
            return $admins;
        }

        foreach ($this->adminGroups[$group]['items'] as $item) {
            $admins[] = $this->getInstance($item['admin']);
        }

        return $admins;
    }

    /**
     * Return the admin related to the given $class.
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

        if (!\is_array($this->adminClasses[$class])) {
            throw new \RuntimeException('Invalid format for the Pool::adminClass property');
        }

        if (\count($this->adminClasses[$class]) > 1) {
            throw new \RuntimeException(sprintf(
                'Unable to find a valid admin for the class: %s, there are too many registered: %s',
                $class,
                implode(', ', $this->adminClasses[$class])
            ));
        }

        return $this->getInstance($this->adminClasses[$class][0]);
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
     * ie : sonata.news.admin.post|sonata.news.admin.comment => return the child class of post.
     *
     * @param string $adminCode
     *
     * @throws \InvalidArgumentException if the root admin code is an empty string
     *
     * @return AdminInterface|false
     */
    public function getAdminByAdminCode($adminCode)
    {
        if (!\is_string($adminCode)) {
            @trigger_error(sprintf(
                'Passing a non string value as argument 1 for %s() is deprecated since sonata-project/admin-bundle 3.51 and will cause a \TypeError in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);

            return false;

            // NEXT_MAJOR : remove this condition check and declare "string" as type without default value for argument 1
        }

        $codes = explode('|', $adminCode);
        $code = trim(array_shift($codes));

        if ('' === $code) {
            throw new \InvalidArgumentException('Root admin code must contain a valid admin reference, empty string given.');
        }

        $admin = $this->getInstance($code);

        foreach ($codes as $code) {
            if (!\in_array($code, $this->adminServiceIds, true)) {
                @trigger_error(sprintf(
                    'Passing an invalid admin code as argument 1 for %s() is deprecated since sonata-project/admin-bundle 3.50 and will throw an exception in 4.0.',
                    __METHOD__
                ), E_USER_DEPRECATED);

                // NEXT_MAJOR : throw `\InvalidArgumentException` instead
            }

            if (!$admin->hasChild($code)) {
                @trigger_error(sprintf(
                    'Passing an invalid admin hierarchy inside argument 1 for %s() is deprecated since sonata-project/admin-bundle 3.51 and will throw an exception in 4.0.',
                    __METHOD__
                ), E_USER_DEPRECATED);

                // NEXT_MAJOR : remove the previous `trigger_error()` call, uncomment the following exception and declare AdminInterface as return type
                // throw new \InvalidArgumentException(sprintf(
                //    'Argument 1 passed to %s() must contain a valid admin hierarchy, "%s" is not a valid child for "%s"',
                //    __METHOD__,
                //    $code,
                //    $admin->getCode()
                // ));

                return false;
            }

            $admin = $admin->getChild($code);
        }

        return $admin;
    }

    /**
     * Checks if an admin with a certain admin code exists.
     */
    final public function hasAdminByAdminCode(string $adminCode): bool
    {
        try {
            if (!$this->getAdminByAdminCode($adminCode) instanceof AdminInterface) {
                // NEXT_MAJOR : remove `if (...instanceof...) { return false; }` as getAdminByAdminCode() will then always throw an \InvalidArgumentException when somethings wrong
                return false;
            }
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns a new admin instance depends on the given code.
     *
     * @param string $id
     *
     * @throws \InvalidArgumentException
     *
     * @return AdminInterface
     */
    public function getInstance($id)
    {
        if (!\in_array($id, $this->adminServiceIds, true)) {
            $msg = sprintf('Admin service "%s" not found in admin pool.', $id);
            $shortest = -1;
            $closest = null;
            $alternatives = [];
            foreach ($this->adminServiceIds as $adminServiceId) {
                $lev = levenshtein($id, $adminServiceId);
                if ($lev <= $shortest || $shortest < 0) {
                    $closest = $adminServiceId;
                    $shortest = $lev;
                }
                if ($lev <= \strlen($adminServiceId) / 3 || false !== strpos($adminServiceId, $id)) {
                    $alternatives[$adminServiceId] = $lev;
                }
            }
            if (null !== $closest) {
                asort($alternatives);
                unset($alternatives[$closest]);
                $msg = sprintf(
                    'Admin service "%s" not found in admin pool. Did you mean "%s" or one of those: [%s]?',
                    $id,
                    $closest,
                    implode(', ', array_keys($alternatives))
                );
            }
            throw new \InvalidArgumentException($msg);
        }

        $admin = $this->container->get($id);

        if (!$admin instanceof AdminInterface) {
            throw new InvalidArgumentException(sprintf('Found service "%s" is not a valid admin service', $id));
        }

        return $admin;
    }

    /**
     * @return ContainerInterface|null
     */
    public function getContainer()
    {
        return $this->container;
    }

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

    final public function setTemplateRegistry(MutableTemplateRegistryInterface $templateRegistry)
    {
        $this->templateRegistry = $templateRegistry;
    }

    /**
     * @deprecated since sonata-project/admin-bundle 3.34, will be dropped in 4.0. Use TemplateRegistry "sonata.admin.global_template_registry" instead
     */
    public function setTemplates(array $templates)
    {
        // NEXT MAJOR: Remove this line
        $this->templates = $templates;

        $this->templateRegistry->setTemplates($templates);
    }

    /**
     * @deprecated since sonata-project/admin-bundle 3.34, will be dropped in 4.0. Use TemplateRegistry "sonata.admin.global_template_registry" instead
     *
     * @return array
     */
    public function getTemplates()
    {
        return $this->templateRegistry->getTemplates();
    }

    /**
     * @deprecated since sonata-project/admin-bundle 3.34, will be dropped in 4.0. Use TemplateRegistry "sonata.admin.global_template_registry" instead
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getTemplate($name)
    {
        return $this->templateRegistry->getTemplate($name);
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
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return $default;
    }

    public function getPropertyAccessor()
    {
        if (null === $this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        return $this->propertyAccessor;
    }
}
