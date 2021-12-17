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

trait AutoConfiguredORMAdminTrait
{
    use AutoConfiguredAdminTrait {
        AutoConfiguredAdminTrait::getAdminConfiguration as parentTraitAdminConfiguration;
    }

    public static function getAdminConfiguration(): array
    {
        return array_merge(static::parentTraitAdminConfiguration(), [
            'manager_type' => 'orm',
            'class' => static::getDefaultClass(),
        ]);
    }

    public static function getDefaultClass() : string
    {
        $explode = explode("\\", static::class);
        return "App\\Entity\\" . str_replace("Admin", "", end($explode));
    }
}
