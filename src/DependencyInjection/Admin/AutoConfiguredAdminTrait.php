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

namespace Sonata\AdminBundle\DependencyInjection\Admin;

trait AutoConfiguredAdminTrait
{
    public static function getAdminConfiguration(): AdminConfiguration
    {
        $conf = new AdminConfiguration();
        $conf->class = static::getDefaultClass();
        $conf->label = static::getDefaultLabel();
        $conf->managerType = static::getDefaultManagerType($conf->class);

        return $conf;
    }

    protected static function getDefaultLabel(): string
    {
        $explode = explode("\\", static::class);
        return str_replace("Admin", "", end($explode));
    }

    protected static function getDefaultClass(): ?string
    {
        $explode = explode("\\", static::class);
        $name = str_replace("Admin", "", end($explode));
        
        $ormName = sprintf("App\\Entity\\%s", $name);
        if (class_exists($ormName)) {
            return $ormName;
        }

        $odmName = sprintf("App\\Document\\%s", $name);
        if (class_exists($odmName)) {
            return $odmName;
        }

        return null;
    }

    protected static function getDefaultManagerType(?string $class)
    {
        if(!$class){
            return null;
        }

        if (preg_match("/\\\Document\\\/", $class)) {
            return 'odm';
        }
        if (preg_match("/\\\Entity\\\/", $class)) {
            return 'orm';
        }

        return null;
    }
}
