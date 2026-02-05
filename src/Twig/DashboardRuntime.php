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

namespace SensioLabs\AdminBundle\Twig;

use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Dashboard\DashboardControllerInterface;
use SensioLabs\AdminBundle\Dashboard\MenuItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class DashboardRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private DashboardControllerInterface $dashboardController,
        private Pool $pool,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return MenuItem[]
     */
    public function getMenuItems(): array
    {
        return $this->dashboardController->getMenuItems();
    }

    public function getMenuItemUrl(MenuItem $item): ?string
    {
        return match ($item->getType()) {
            'dashboard' => $this->urlGenerator->generate('sensiolabs_admin_dashboard'),
            'crud' => $this->getAdminListUrl($item->getAdminCode()),
            'route' => $this->urlGenerator->generate($item->getRouteName() ?? '', $item->getRouteParameters()),
            'url' => $item->getUrl(),
            default => null,
        };
    }

    private function getAdminListUrl(?string $adminCode): ?string
    {
        if (null === $adminCode) {
            return null;
        }

        try {
            $admin = $this->pool->getInstance($adminCode);

            return $admin->generateUrl('list');
        } catch (\Exception) {
            return null;
        }
    }
}
