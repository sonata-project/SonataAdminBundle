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

namespace Sonata\AdminBundle\Command;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class Validators
{
    /**
     * @throws \InvalidArgumentException
     */
    public static function validateUsername(?string $username): string
    {
        if (null === $username) {
            throw new \InvalidArgumentException('The username must be set');
        }

        return $username;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @phpstan-return class-string
     */
    public static function validateClass(string $class): string
    {
        $class = str_replace('/', '\\', $class);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('The class "%s" does not exist.', $class));
        }

        return $class;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function validateAdminClassBasename(string $adminClassBasename): string
    {
        $adminClassBasename = str_replace('/', '\\', $adminClassBasename);

        if (false !== strpos($adminClassBasename, ':')) {
            throw new \InvalidArgumentException(sprintf(
                'The admin class name must not contain a ":" (colon sign)'
                .' ("%s" given, expecting something like PostAdmin")',
                $adminClassBasename
            ));
        }

        return $adminClassBasename;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function validateControllerClassBasename(string $controllerClassBasename): string
    {
        $controllerClassBasename = str_replace('/', '\\', $controllerClassBasename);

        if (false !== strpos($controllerClassBasename, ':')) {
            throw new \InvalidArgumentException(sprintf(
                'The controller class name must not contain a ":" (colon sign)'
                .' ("%s" given, expecting something like PostAdminController")',
                $controllerClassBasename
            ));
        }

        if ('Controller' !== substr($controllerClassBasename, -10)) {
            throw new \InvalidArgumentException('The controller class name must end with "Controller".');
        }

        return $controllerClassBasename;
    }

    public static function validateServicesFile(string $servicesFile): string
    {
        return trim($servicesFile, '/');
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function validateServiceId(string $serviceId): string
    {
        if (1 === preg_match('/[^A-Za-z\._0-9]/', $serviceId, $matches)) {
            throw new \InvalidArgumentException(sprintf(
                'Service ID "%s" contains invalid character "%s".',
                $serviceId,
                $matches[0]
            ));
        }

        return $serviceId;
    }
}
