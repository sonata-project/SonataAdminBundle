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
    public static function getAdminConfiguration(): array
    {
        return [
            'code' => static::getDefaultCode(),
            'class' => null,
            'controller' => null,
            'label' => static::getDefaultLabel(),
            'show_in_dashboard' => true,
            'group' => 'admin',
            'label_catalogue' => null,
            'icon' => null,
            'on_top' => false,
            'keep_open' => false,
            'manager_type' => null
        ];
    }

    public static function getDefaultCode(): string
    {
        $code = "";
        $explode = explode("\\", str_replace("App\\", "", static::class));
        $count = count($explode);
        foreach ($explode as $i => $part) {
            $part = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $part)) . ".";
            if ($count - 1 === $i) {
                $part = str_replace("_admin.", "", $part);
            }

            $code .= $part;
        }

        return $code;
    }

    public static function getDefaultLabel(): string
    {
        $explode = explode("\\", static::class);
        return str_replace("Admin", "", end($explode));
    }
}
