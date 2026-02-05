<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Twig\Extension;

use SensioLabs\AdminBundle\Twig\DashboardRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class DashboardExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sensiolabs_admin_menu_items', [DashboardRuntime::class, 'getMenuItems']),
            new TwigFunction('sensiolabs_admin_menu_item_url', [DashboardRuntime::class, 'getMenuItemUrl']),
            new TwigFunction('sensiolabs_admin_menu_item_granted', [DashboardRuntime::class, 'isMenuItemGranted']),
            new TwigFunction('sensiolabs_admin_menu_granted_children', [DashboardRuntime::class, 'getGrantedChildren']),
            new TwigFunction('sensiolabs_admin_menu_has_granted_children', [DashboardRuntime::class, 'hasGrantedChildren']),
        ];
    }
}
