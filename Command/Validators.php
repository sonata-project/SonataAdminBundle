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
}
