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

namespace SensioLabs\AdminBundle\Dashboard;

/**
 * Default dashboard controller that auto-generates menu items from registered admins.
 *
 * This is used when no custom dashboard controller is registered.
 */
final class DefaultDashboardController extends AbstractDashboardController
{
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'lucide:home');

        $adminCodes = $this->getPool()->getAdminServiceCodes();

        // Auto-generate menu items from all registered admins
        foreach ($adminCodes as $adminCode) {
            $admin = $this->getPool()->getInstance($adminCode);

            // Skip child admins (they are accessed through their parent)
            if ($admin->isChild()) {
                continue;
            }

            yield MenuItem::linkToCrud(
                $admin->getLabel() ?? $adminCode,
                'lucide:folder',
                $admin->getCode()
            );
        }
    }

    public function configureDashboard(): array
    {
        return [
            'title' => 'Administration',
        ];
    }
}
