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

use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Templating\TemplateRegistryInterface;
use Twig\Environment;

/**
 * Default dashboard controller that auto-generates menu items from registered admins.
 */
final class DefaultDashboardController extends AbstractDashboardController
{
    public function __construct(
        Pool $pool,
        TemplateRegistryInterface $templateRegistry,
        Environment $twig,
    ) {
        parent::__construct($pool, $templateRegistry, $twig);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fas fa-home');

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
                'fas fa-folder',
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
