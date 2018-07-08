<?php

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
class Validators
{
    /**
     * @static
     *
     * @param string|null $username
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public static function validateUsername($username)
    {
        if (null === $username) {
            throw new \InvalidArgumentException('The username must be set');
        }

        return $username;
    }

    /**
     * @static
     *
     * @param string $shortcut
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public static function validateEntityName($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf(
                'The entity name must contain a ":" (colon sign) '
                .'("%s" given, expecting something like AcmeBlogBundle:Post)',
                $entity
            ));
        }

        return [substr($entity, 0, $pos), substr($entity, $pos + 1)];
    }

    /**
     * @static
     *
     * @param string $class
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function validateClass($class)
    {
        $class = str_replace('/', '\\', $class);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('The class "%s" does not exist.', $class));
        }

        return $class;
    }

    /**
     * @static
     *
     * @param string $adminClassBasename
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function validateAdminClassBasename($adminClassBasename)
    {
        $adminClassBasename = str_replace('/', '\\', $adminClassBasename);

        if (false !== strpos($adminClassBasename, ':')) {
            throw new \InvalidArgumentException(sprintf(
                'The admin class name must not contain a ":" (colon sign) '
                .'("%s" given, expecting something like PostAdmin")',
                $adminClassBasename
            ));
        }

        return $adminClassBasename;
    }

    /**
     * @static
     *
     * @param string $controllerClassBasename
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function validateControllerClassBasename($controllerClassBasename)
    {
        $controllerClassBasename = str_replace('/', '\\', $controllerClassBasename);

        if (false !== strpos($controllerClassBasename, ':')) {
            throw new \InvalidArgumentException(sprintf(
                'The controller class name must not contain a ":" (colon sign) ("%s" given, '
                .'expecting something like PostAdminController")',
                $controllerClassBasename
            ));
        }

        if ('Controller' != substr($controllerClassBasename, -10)) {
            throw new \InvalidArgumentException('The controller class name must end with "Controller".');
        }

        return $controllerClassBasename;
    }

    /**
     * @static
     *
     * @param string $servicesFile
     *
     * @return string
     */
    public static function validateServicesFile($servicesFile)
    {
        return trim($servicesFile, '/');
    }

    /**
     * @static
     *
     * @param string $serviceId
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function validateServiceId($serviceId)
    {
        if (preg_match('/[^A-Za-z\._0-9]/', $serviceId, $matches)) {
            throw new \InvalidArgumentException(sprintf(
                'Service ID "%s" contains invalid character "%s".',
                $serviceId,
                $matches[0]
            ));
        }

        return $serviceId;
    }
}
