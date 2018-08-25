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

use Sonata\AdminBundle\Exception\BadNameFormat;
use Sonata\AdminBundle\Exception\BadEntityNameFormat;
use Sonata\AdminBundle\Exception\MissingClass;
use Sonata\AdminBundle\Exception\MissingControllerSuffix;
use Sonata\AdminBundle\Exception\MissingUsername;
use Sonata\AdminBundle\Exception\ServiceContainsInvalidCharacters;

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
            throw MissingUsername::create();
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
            throw BadEntityNameFormat::create($entity);
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
            throw MissingClass::create($class);
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
            throw BadNameFormat::forAdmin($adminClassBasename);
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
            throw BadNameFormat::forController($controllerClassBasename);
        }

        if ('Controller' != substr($controllerClassBasename, -10)) {
            throw MissingControllerSuffix::create();
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
            throw ServiceContainsInvalidCharacters::create($serviceId, $matches[0]);
        }

        return $serviceId;
    }
}
