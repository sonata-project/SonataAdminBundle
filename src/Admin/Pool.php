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

use Psr\Container\ContainerInterface;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;

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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class Pool
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string[]
     */
    private $adminServiceIds = [];

    /**
     * @var array
     * @phpstan-var array<string, array<string, mixed>>
     * @psalm-var array<string, Group>
     */
    private $adminGroups = [];

    /**
     * @var array<string, string[]>
     * @phpstan-var array<class-string, string[]>
     */
    private $adminClasses = [];

    /**
     * NEXT_MAJOR: change to TemplateRegistryInterface.
     *
     * @var MutableTemplateRegistryInterface
     */
    private $templateRegistry;

    /**
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
        $optionsOrAdminClasses = []
    ) {
        $this->container = $container;
        $this->adminServiceIds = $titleOrAdminServiceIds;
        $this->adminGroups = $logoTitleOrAdminGroups;
        $this->adminClasses = $optionsOrAdminClasses;
    }

    /**
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
    public function getDashboardGroups(): array
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
     * Return the admin related to the given $class.
     *
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return AdminInterface<T>
     */
    public function getAdminByClass(string $class): AdminInterface
    {
        if (!$this->hasAdminByClass($class)) {
            throw new \LogicException(sprintf('Pool has no admin for the class %s.', $class));
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
     * @phpstan-param class-string $class
     */
    public function hasAdminByClass(string $class): bool
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
     * @throws \InvalidArgumentException if the root admin code is an empty string
     */
    public function getAdminByAdminCode(string $adminCode): AdminInterface
    {
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
                throw new \InvalidArgumentException(sprintf(
                    'Argument 1 passed to %s() must contain a valid admin reference, "%s" found at "%s".',
                    __METHOD__,
                    $code,
                    $adminCode
                ));
            }

            if (!$admin->hasChild($code)) {
                throw new \InvalidArgumentException(sprintf(
                    'Argument 1 passed to %s() must contain a valid admin hierarchy, "%s" is not a valid child for "%s"',
                    __METHOD__,
                    $code,
                    $admin->getCode()
                ));
            }

            $admin = $admin->getChild($code);
        }

        return $admin;
    }

    /**
     * Checks if an admin with a certain admin code exists.
     */
    public function hasAdminByAdminCode(string $adminCode): bool
    {
        try {
            $this->getAdminByAdminCode($adminCode);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns a new admin instance depends on the given code.
     *
     * @throws \InvalidArgumentException
     */
    public function getInstance(string $id): AdminInterface
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
     * @deprecated since sonata-project/admin-bundle 3.86, will be dropped in 4.0. Pass $adminGroups as argument 3
     * to the __construct method instead.
     *
     * @phpstan-param array<string, array<string, mixed>> $adminGroups
     * @psalm-param array<string, Group> $adminGroups
     */
    public function setAdminGroups(array $adminGroups): void
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'Method "%s()" is deprecated since sonata-project/admin-bundle 3.86 and will be removed in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        $this->adminGroups = $adminGroups;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getAdminGroups(): array
    {
        return $this->adminGroups;
    }

    /**
     * @param string[] $adminServiceIds
     *                                  NEXT_MAJOR: Remove this method
     *
     * @deprecated since sonata-project/admin-bundle 3.86, will be dropped in 4.0. Pass $adminGroups as argument 2
     * to the __construct method instead.
     */
    public function setAdminServiceIds(array $adminServiceIds): void
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'Method "%s()" is deprecated since sonata-project/admin-bundle 3.86 and will be removed in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        $this->adminServiceIds = $adminServiceIds;
    }

    /**
     * @return string[]
     */
    public function getAdminServiceIds(): array
    {
        return $this->adminServiceIds;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 3.86, will be dropped in 4.0. Pass $adminGroups as argument 4
     * to the __construct method instead.
     *
     * @param array<string, string[]> $adminClasses
     *
     * @phpstan-param array<class-string, string[]> $adminClasses
     */
    public function setAdminClasses(array $adminClasses): void
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'Method "%s()" is deprecated since sonata-project/admin-bundle 3.86 and will be removed in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        $this->adminClasses = $adminClasses;
    }

    /**
     * @return array<string, string[]>
     *
     * @phpstan-return array<class-string, string[]>
     */
    public function getAdminClasses(): array
    {
        return $this->adminClasses;
    }

    /**
     * NEXT_MAJOR: change to TemplateRegistryInterface.
     */
    public function setTemplateRegistry(MutableTemplateRegistryInterface $templateRegistry): void
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
}
