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

interface AutoConfiguredAdminInterface
{
    /**
     * Return an array wuth all parameters required to configure admin:
     * [
     *    code: ?string
     *    class: string
     *    controller: ?string
     *    label: ?string
     *    show_in_dashboard: bool
     *    group: string
     *    label_catalog: ?string
     *    icon: ?string
     *    on_top: bool
     *    keep_open: bool
     *    manager_type: string
     * ]
     *
     * @return array
     */
    public static function getAdminConfiguration() : array;
}