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

use Sonata\AdminBundle\SonataConfiguration;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @psalm-type Group = array{
 *  label: string,
 *  label_catalogue: string,
 *  icon: string,
 *  item_adds: array,
 *  items: array<array-key, array{
 *      admin?: string,
 *      label?: string,
 *      roles: list<string>,
 *      route?: string,
 *      router_absolute: bool,
 *      route_params: array<string, string>
 *  }>,
 *  keep_open: bool,
 *  on_top: bool,
 *  roles: list<string>
 * }
 *
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
     * @phpstan-var array<string, array<string, mixed>>
     * @psalm-var array<string, Group>
     */
    protected $adminGroups = [];

    /**
     * @var array<string, string[]>
     *
     * @phpstan-var array<class-string, string[]>
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
     * NEXT_MAJOR: Remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.83, will be dropped in 4.0.
     *
     * @var string
     */
    protected $title;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.83, will be dropped in 4.0.
     *
     * @var string
     */
    protected $titleLogo;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.83, will be dropped in 4.0.
     *
     * @var array
     */
    protected $options = [];

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var PropertyAccessorInterface
     *
     * @deprecated since sonata-project/admin-bundle 3.82, will be dropped in 4.0.
     */
    protected $propertyAccessor;

    /**
     * NEXT_MAJOR: change to TemplateRegistryInterface.
     *
     * @var MutableTemplateRegistryInterface
     */
    private $templateRegistry;

    /**
     * NEXT_MAJOR: Remove $propertyAccessor argument.
     * NEXT_MAJOR: Rename $titleOrAdminServiceIds to $adminServices, $logoTitleOrAdminGroups to $adminGroups and
     * $optionsOrAdminClasses to $adminClasses and add "array" type declaration.
     *
     * @param string|array $titleOrAdminServiceIds
     * @param string|array $logoTitleOrAdminGroups
     * @param array        $optionsOrAdminClasses
     */
    public function __construct(
        ContainerInterface $container,
        $titleOrAdminServiceIds = [],
        $logoTitleOrAdminGroups = [],
        $optionsOrAdminClasses = [],
        ?PropertyAccessorInterface $propertyAccessor = null
    ) {
        $this->container = $container;

        // NEXT_MAJOR: Uncomment the following lines
        // $this->adminServiceIds = $titleOrAdminServiceIds;
        // $this->adminGroups = $logoTitleOrAdminGroups;
        // $this->adminClasses = $optionsOrAdminClasses;

        // NEXT_MAJOR: Remove this block.
        if (\is_array($titleOrAdminServiceIds)) {
            $this->adminServiceIds = $titleOrAdminServiceIds;
        } else {
            @trigger_error(sprintf(
                'Passing other type than array as argument 2 to "%s()" is deprecated since'
                .' sonata-project/admin-bundle 3.x and will throw "%s" exception in 4.0.',
                __METHOD__,
                \TypeError::class
            ), E_USER_DEPRECATED);

            $this->title = $titleOrAdminServiceIds;
        }

        // NEXT_MAJOR: Remove this block.
        if (\is_array($logoTitleOrAdminGroups)) {
            $this->adminGroups = $logoTitleOrAdminGroups;
        } else {
            @trigger_error(sprintf(
                'Passing other type than array as argument 3 to "%s()" is deprecated since'
                .' sonata-project/admin-bundle 3.x and will throw "%s" exception in 4.0.',
                __METHOD__,
                \TypeError::class
            ), E_USER_DEPRECATED);

            $this->titleLogo = $logoTitleOrAdminGroups;
        }

        // NEXT_MAJOR: Remove this block.
        if (\is_array($titleOrAdminServiceIds) && \is_array($logoTitleOrAdminGroups)) {
            $this->adminClasses = $optionsOrAdminClasses;
        } else {
            $this->options = $optionsOrAdminClasses;
        }

        // NEXT_MAJOR: Remove this block.
        if (null !== $propertyAccessor) {
            @trigger_error(sprintf(
                'Passing an "%s" instance as argument 4 to "%s()" is deprecated since sonata-project/admin-bundle 3.82.',
                PropertyAccessorInterface::class,
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        // NEXT_MAJOR: Remove next line.
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @internal
     */
    public function setDeprecatedPropertiesForBC(string $title, string $titleLogo, array $options): void
    {
        $this->title = $title;
        $this->titleLogo = $titleLogo;
        $this->options = $options;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.83 and will be removed in 4.0.
     *
     * @return array
     */
    public function getGroups()
    {
        @trigger_error(sprintf(
            'Method "%s()" is deprecated since sonata-project/admin-bundle 3.83 and will be removed in version 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        $groups = $this->adminGroups;

        foreach ($this->adminGroups as $name => $adminGroup) {
            foreach ($adminGroup as $id => $options) {
                $groups[$name][$id] = $this->getInstance($id);
            }
        }

        return $groups;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.83 and will be removed in 4.0.
     *
     * Returns whether an admin group exists or not.
     *
     * @param string $group
     *
     * @return bool
     */
    public function hasGroup($group)
    {
        @trigger_error(sprintf(
            'Method "%s()" is deprecated since sonata-project/admin-bundle 3.83 and will be removed in version 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        return isset($this->adminGroups[$group]);
    }

    /**
     * @return array
     * @phpstan-return array<string, array{
     *  label: string,
     *  label_catalogue: string,
     *  icon: string,
     *  item_adds: array,
     *  items: array<array-key, AdminInterface>,
     *  keep_open: bool,
     *  on_top: bool,
     *  roles: list<string>
     * }>
     */
    public function getDashboardGroups()
    {
        $groups = $this->adminGroups;

        foreach ($this->adminGroups as $name => $adminGroup) {
            if (isset($adminGroup['items'])) {
                foreach ($adminGroup['items'] as $key => $item) {
                    // Only Admin Group should be returned
                    if (isset($item['admin']) && !empty($item['admin'])) {
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
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.83 and will be removed in 4.0.
     *
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
        @trigger_error(sprintf(
            'Method "%s()" is deprecated since sonata-project/admin-bundle 3.83 and will be removed in version 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (!isset($this->adminGroups[$group])) {
            throw new \InvalidArgumentException(sprintf('Group "%s" not found in admin pool.', $group));
        }

        $admins = [];

        if (!isset($this->adminGroups[$group]['items'])) {
            return $admins;
        }

        foreach ($this->adminGroups[$group]['items'] as $item) {
            if (isset($item['admin']) && !empty($item['admin'])) {
                $admins[] = $this->getInstance($item['admin']);
            }
        }

        return $admins;
    }

    /**
     * Return the admin related to the given $class.
     *
     * @param string $class
     *
     * @return AdminInterface|null
     *
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return AdminInterface<T>|null
     */
    public function getAdminByClass($class)
    {
        if (!$this->hasAdminByClass($class)) {
            @trigger_error(sprintf(
                'Calling %s() when there is no admin for the class %s is deprecated since sonata-project/admin-bundle'
                .' 3.69 and will throw an exception in 4.0. Use %s::hasAdminByClass() to know if the admin exists.',
                __METHOD__,
                $class,
                __CLASS__
            ), E_USER_DEPRECATED);

            // NEXT_MAJOR : remove the previous `trigger_error()` call, the `return null` statement,
            // uncomment the following exception and declare AdminInterface as return type
            //
            // throw new \LogicException(sprintf('Pool has no admin for the class %s.', $class));

            return null;
        }

        if (!$this->hasSingleAdminByClass($class)) {
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
     *
     * @phpstan-param class-string $class
     */
    public function hasAdminByClass($class)
    {
        return isset($this->adminClasses[$class]);
    }

    /**
     * @phpstan-param class-string $class
     */
    public function hasSingleAdminByClass(string $class): bool
    {
        if (!$this->hasAdminByClass($class)) {
            return false;
        }

        return 1 === \count($this->adminClasses[$class]);
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
                'Passing a non string value as argument 1 for %s() is deprecated since'
                .' sonata-project/admin-bundle 3.51 and will cause a %s in 4.0.',
                __METHOD__,
                \TypeError::class
            ), E_USER_DEPRECATED);

            return false;

            // NEXT_MAJOR : remove this condition check and declare "string" as type without default value for argument 1
        }

        $codes = explode('|', $adminCode);
        $code = trim(array_shift($codes));

        if ('' === $code) {
            throw new \InvalidArgumentException(
                'Root admin code must contain a valid admin reference, empty string given.'
            );
        }

        $admin = $this->getInstance($code);

        foreach ($codes as $code) {
            if (!\in_array($code, $this->adminServiceIds, true)) {
                @trigger_error(sprintf(
                    'Passing an invalid admin code as argument 1 for %s() is deprecated since'
                    .' sonata-project/admin-bundle 3.50 and will throw an exception in 4.0.',
                    __METHOD__
                ), E_USER_DEPRECATED);

                // NEXT_MAJOR : throw `\InvalidArgumentException` instead
            }

            if (!$admin->hasChild($code)) {
                @trigger_error(sprintf(
                    'Passing an invalid admin hierarchy inside argument 1 for %s() is deprecated since'
                    .' sonata-project/admin-bundle 3.51 and will throw an exception in 4.0.',
                    __METHOD__
                ), E_USER_DEPRECATED);

                // NEXT_MAJOR : remove the previous `trigger_error()` call, uncomment the following exception and declare AdminInterface as return type
                // throw new \InvalidArgumentException(sprintf(
                //    'Argument 1 passed to %s() must contain a valid admin hierarchy,'
                //    .' "%s" is not a valid child for "%s"',
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
            throw new \InvalidArgumentException(sprintf('Found service "%s" is not a valid admin service', $id));
        }

        return $admin;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.77.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
            @trigger_error(sprintf(
                'Method "%s()" is deprecated since sonata-project/admin-bundle 3.77 and will be removed in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        return $this->container;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.x, will be dropped in 4.0. Pass $adminGroups as argument 3
     * to the __construct method instead.
     *
     * @phpstan-param array<string, array<string, mixed>> $adminGroups
     * @psalm-param array<string, Group> $adminGroups
     *
     * @return void
     */
    public function setAdminGroups(array $adminGroups)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'Method "%s()" is deprecated since sonata-project/admin-bundle 3.x and will be removed in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

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
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.x, will be dropped in 4.0. Pass $adminGroups as argument 2
     * to the __construct method instead.
     *
     * @return void
     */
    public function setAdminServiceIds(array $adminServiceIds)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'Method "%s()" is deprecated since sonata-project/admin-bundle 3.x and will be removed in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        $this->adminServiceIds = $adminServiceIds;
    }

    /**
     * @return string[]
     */
    public function getAdminServiceIds()
    {
        return $this->adminServiceIds;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.x, will be dropped in 4.0. Pass $adminGroups as argument 4
     * to the __construct method instead.
     *
     * @param array<string, string[]> $adminClasses
     *
     * @phpstan-param array<class-string, string[]> $adminClasses
     *
     * @return void
     */
    public function setAdminClasses(array $adminClasses)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'Method "%s()" is deprecated since sonata-project/admin-bundle 3.x and will be removed in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        $this->adminClasses = $adminClasses;
    }

    /**
     * @return array<string, string[]>
     *
     * @phpstan-return array<class-string, string[]>
     */
    public function getAdminClasses()
    {
        return $this->adminClasses;
    }

    /**
     * NEXT_MAJOR: change to TemplateRegistryInterface.
     */
    final public function setTemplateRegistry(MutableTemplateRegistryInterface $templateRegistry): void
    {
        $this->templateRegistry = $templateRegistry;
    }

    /**
     * @deprecated since sonata-project/admin-bundle 3.34, will be dropped in 4.0. Use TemplateRegistry "sonata.admin.global_template_registry" instead
     *
     * @return void
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
     * @return array<string, string>
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
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.83, will be dropped in 4.0.
     *
     * @return string
     */
    public function getTitleLogo()
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated since version 3.83 and will be removed in 4.0.'
            .' Use "%s::getLogo()" instead.',
            __METHOD__,
            SonataConfiguration::class
        ), E_USER_DEPRECATED);

        return $this->titleLogo;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.83, will be dropped in 4.0.
     *
     * @return string
     */
    public function getTitle()
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated since version 3.83 and will be removed in 4.0.'
            .' Use "%s::getTitle()" instead.',
            __METHOD__,
            SonataConfiguration::class
        ), E_USER_DEPRECATED);

        return $this->title;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.83, will be dropped in 4.0.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated since version 3.83 and will be removed in 4.0.'
            .' Use "%s::getOption()" instead.',
            __METHOD__,
            SonataConfiguration::class
        ), E_USER_DEPRECATED);

        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return $default;
    }

    /**
     * @deprecated since sonata-project/admin-bundle 3.82, will be dropped in 4.0. Use Symfony "PropertyAccess" instead.
     */
    public function getPropertyAccessor()
    {
        @trigger_error(sprintf(
            'The "%s" method is deprecated since version 3.82 and will be removed in 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        if (null === $this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        return $this->propertyAccessor;
    }
}
