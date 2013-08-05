<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Command;

class Validators
{
    /**
     * @static
     *
     * @param string $username
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public static function validateUsername($username)
    {
        if (is_null($username)) {
            throw new \InvalidArgumentException('The username must be set');
        }

        return $username;
    }

    /**
     * @static
     *
     * @param string $shortcut
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function validateEntityName($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf('The entity name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Post)', $entity));
        }

        return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }

    /**
     * @static
     *
     * @param string $controllerClassName
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function validateControllerClassName($controllerClassName)
    {
        $controllerClassName = str_replace('/', '\\', $controllerClassName);

        if (false !== strpos($controllerClassName, ':')) {
            throw new \InvalidArgumentException(sprintf('The controller class name must not contain a : ("%s" given, expecting something like PostAdminController")', $controllerClassName));
        }

        if (substr($controllerClassName, -10) != 'Controller') {
            throw new \InvalidArgumentException('The controller class name must end with Controller.');
        }

        return $controllerClassName;
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
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function validateServiceId($serviceId)
    {
        if (preg_match('/[^A-z\._0-9]/', $serviceId, $matches)) {
            throw new \InvalidArgumentException(sprintf(
                'Service ID "%s" contains invalid character "%s".',
                $serviceId,
                $matches[0]
            ));
        }

        return $serviceId;
    }
}
