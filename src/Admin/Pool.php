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
use Sonata\AdminBundle\Exception\AdminClassNotFoundException;
use Sonata\AdminBundle\Exception\AdminCodeNotFoundException;
use Sonata\AdminBundle\Exception\TooManyAdminClassException;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-type Item = array{
 *     label: string,
 *     roles: list<string>,
 *     route: string,
 *     route_absolute: bool,
 *     route_params: array<string, string>
 * }|array{
 *     admin: string,
 *     roles: list<string>,
 *     route_absolute: bool,
 *     route_params: array<string, string>
 * }
 * NEXT_MAJOR: Remove the label_catalogue key.
 * @phpstan-type Group = array{
 *     label: string,
 *     translation_domain: string,
 *     label_catalogue?: string,
 *     icon: string,
 *     items: list<Item>,
 *     keep_open: bool,
 *     on_top: bool,
 *     provider?: string,
 *     roles: list<string>
 * }
 */
final class Pool
{
    public const DEFAULT_ADMIN_KEY = 'default';

    /**
     * @param string[]                            $adminServiceCodes
     * @param array<string, array<string, mixed>> $adminGroups
     * @param array<class-string, string[]>       $adminClasses
     *
     * @phpstan-param array<string, Group> $adminGroups
     */
    public function __construct(
        private ContainerInterface $container,
        private array $adminServiceCodes = [],
        private array $adminGroups = [],
        private array $adminClasses = []
    ) {
    }

    /**
     * NEXT_MAJOR: Remove the label_catalogue key.
     *
     * @phpstan-return array<string, array{
     *  label: string,
     *  label_catalogue?: string,
     *  translation_domain: string,
     *  icon: string,
     *  items: list<AdminInterface<object>>,
     *  keep_open: bool,
     *  on_top: bool,
     *  provider?: string,
     *  roles: list<string>
     * }>
     */
    public function getDashboardGroups(): array
    {
        $groups = [];

        foreach ($this->adminGroups as $name => $adminGroup) {
            $items = [];
            foreach ($adminGroup['items'] as $item) {
                // NEXT_MAJOR: Remove the '' check
                if (!isset($item['admin']) || '' === $item['admin']) {
                    continue;
                }

                $admin = $this->getInstance($item['admin']);

                // NEXT_MAJOR: Keep the "if" part.
                // @phpstan-ignore-next-line
                if (method_exists($admin, 'showInDashboard')) {
                    if (!$admin->showInDashboard()) {
                        continue;
                    }
                } else {
                    @trigger_error(sprintf(
                        'Not implementing "%s::showInDashboard()" is deprecated since sonata-project/admin-bundle 4.7'
                        .' and will fail in 5.0.',
                        AdminInterface::class
                    ), \E_USER_DEPRECATED);

                    /**
                     * @psalm-suppress DeprecatedMethod, DeprecatedConstant
                     */
                    if (!$admin->showIn(AbstractAdmin::CONTEXT_DASHBOARD)) {
                        continue;
                    }
                }

                $items[] = $admin;
            }

            if ([] !== $items) {
                $groups[$name] = ['items' => $items] + $adminGroup;
            }
        }

        return $groups;
    }

    /**
     * Return the admin related to the given $class.
     *
     * @throws AdminClassNotFoundException if there is no admin class for the class provided
     * @throws TooManyAdminClassException  if there is multiple admin class for the class provided
     *
     * @phpstan-param class-string $class
     * @phpstan-return AdminInterface<object>
     */
    public function getAdminByClass(string $class): AdminInterface
    {
        if (!$this->hasAdminByClass($class)) {
            throw new AdminClassNotFoundException(sprintf('Pool has no admin for the class %s.', $class));
        }

        if (isset($this->adminClasses[$class][self::DEFAULT_ADMIN_KEY])) {
            return $this->getInstance($this->adminClasses[$class][self::DEFAULT_ADMIN_KEY]);
        }

        if (1 !== \count($this->adminClasses[$class])) {
            throw new TooManyAdminClassException(sprintf(
                'Unable to find a valid admin for the class: %s, there are too many registered: %s.'
                .' Please define a default one with the tag attribute `default: true` in your admin configuration.',
                $class,
                implode(', ', $this->adminClasses[$class])
            ));
        }

        return $this->getInstance(reset($this->adminClasses[$class]));
    }

    /**
     * @phpstan-param class-string $class
     */
    public function hasAdminByClass(string $class): bool
    {
        return isset($this->adminClasses[$class]) && \count($this->adminClasses[$class]) > 0;
    }

    /**
     * Returns an admin class by its Admin code
     * ie : sonata.news.admin.post|sonata.news.admin.comment => return the child class of post.
     *
     * @throws AdminCodeNotFoundException
     *
     * @return AdminInterface<object>
     */
    public function getAdminByAdminCode(string $adminCode): AdminInterface
    {
        $codes = explode('|', $adminCode);
        $rootCode = trim(array_shift($codes));
        $admin = $this->getInstance($rootCode);

        foreach ($codes as $code) {
            if (!\in_array($code, $this->adminServiceCodes, true)) {
                throw new AdminCodeNotFoundException(sprintf(
                    'Argument 1 passed to %s() must contain a valid admin reference, "%s" found at "%s".',
                    __METHOD__,
                    $code,
                    $adminCode
                ));
            }

            if (!$admin->hasChild($code)) {
                throw new AdminCodeNotFoundException(sprintf(
                    'Argument 1 passed to %s() must contain a valid admin hierarchy,'
                    .' "%s" is not a valid child for "%s"',
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
        } catch (\InvalidArgumentException) {
            return false;
        }

        return true;
    }

    /**
     * @throws AdminClassNotFoundException if there is no admin for the field description target model
     * @throws TooManyAdminClassException  if there is too many admin for the field description target model
     * @throws AdminCodeNotFoundException  if the admin_code option is invalid
     *
     * @return AdminInterface<object>
     */
    public function getAdminByFieldDescription(FieldDescriptionInterface $fieldDescription): AdminInterface
    {
        $adminCode = $fieldDescription->getOption('admin_code');

        if (\is_string($adminCode)) {
            return $this->getAdminByAdminCode($adminCode);
        }

        $targetModel = $fieldDescription->getTargetModel();
        if (null === $targetModel) {
            throw new \InvalidArgumentException('The field description has no target model.');
        }

        return $this->getAdminByClass($targetModel);
    }

    /**
     * Returns a new admin instance depends on the given code.
     *
     * @throws AdminCodeNotFoundException if the code is not found in admin pool
     *
     * @return AdminInterface<object>
     */
    public function getInstance(string $code): AdminInterface
    {
        if ('' === $code) {
            throw new \InvalidArgumentException(
                'Admin code must contain a valid admin reference, empty string given.'
            );
        }

        if (!\in_array($code, $this->adminServiceCodes, true)) {
            $msg = sprintf('Admin service "%s" not found in admin pool.', $code);
            $shortest = -1;
            $closest = null;
            $alternatives = [];

            foreach ($this->adminServiceCodes as $adminServiceCode) {
                $lev = levenshtein($code, $adminServiceCode);
                if ($lev <= $shortest || $shortest < 0) {
                    $closest = $adminServiceCode;
                    $shortest = $lev;
                }
                if ($lev <= \strlen($adminServiceCode) / 3 || str_contains($adminServiceCode, $code)) {
                    $alternatives[$adminServiceCode] = $lev;
                }
            }

            if (null !== $closest) {
                asort($alternatives);
                unset($alternatives[$closest]);
                $msg = sprintf(
                    'Admin service "%s" not found in admin pool. Did you mean "%s" or one of those: [%s]?',
                    $code,
                    $closest,
                    implode(', ', array_keys($alternatives))
                );
            }

            throw new AdminCodeNotFoundException($msg);
        }

        $admin = $this->container->get($code);

        if (!$admin instanceof AdminInterface) {
            throw new \InvalidArgumentException(sprintf('Found service "%s" is not a valid admin service', $code));
        }

        return $admin;
    }

    /**
     * @return array<string, array<string, mixed>>
     *
     * @phpstan-return array<string, Group>
     */
    public function getAdminGroups(): array
    {
        return $this->adminGroups;
    }

    /**
     * @return string[]
     */
    public function getAdminServiceCodes(): array
    {
        return $this->adminServiceCodes;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 4.20 will be removed in 5.0 use getAdminServiceCodes() instead.
     *
     * @return string[]
     */
    public function getAdminServiceIds(): array
    {
        return $this->adminServiceCodes;
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
}
