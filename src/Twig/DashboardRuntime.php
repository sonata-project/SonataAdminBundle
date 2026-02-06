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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class DashboardRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private DashboardControllerInterface $dashboardController,
        private Pool $pool,
        private UrlGeneratorInterface $urlGenerator,
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    /**
     * @return MenuItem[]
     */
    public function getMenuItems(): array
    {
        return $this->dashboardController->getMenuItems();
    }

    /**
     * Check if the current user is granted access to view the menu item.
     * For CRUD items, checks if the admin exists and user has list access.
     * For route/url/dashboard items, checks if the user has all required roles.
     */
    public function isMenuItemGranted(MenuItem $item): bool
    {
        // For CRUD items, check admin access and optional roles
        if ('crud' === $item->getType()) {
            $adminCode = $item->getAdminCode();
            if (null === $adminCode) {
                return true;
            }

            try {
                $admin = $this->pool->getInstance($adminCode);

                // Check if admin has list route and user can view it
                if (!$admin->hasRoute('list')) {
                    return false;
                }

                if (!$admin->showInDashboard()) {
                    return false;
                }
            } catch (\Exception $e) {
                // If we can't determine access, show the item and let the controller handle it
                // This prevents hiding menu items due to configuration issues
                return true;
            }

            // Also check explicit roles if specified on the CRUD item
            $roles = $item->getRoles();
            if ([] !== $roles) {
                foreach ($roles as $role) {
                    if (!$this->authorizationChecker->isGranted($role)) {
                        return false;
                    }
                }
            }

            return true;
        }

        // For route, url, and dashboard items, check roles if specified
        $roles = $item->getRoles();
        if ([] === $roles) {
            return true;
        }

        // User must have ALL specified roles
        foreach ($roles as $role) {
            if (!$this->authorizationChecker->isGranted($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Filter children of a submenu to only include granted items.
     *
     * @return MenuItem[]
     */
    public function getGrantedChildren(MenuItem $item): array
    {
        if (!$item->isSubMenu()) {
            return [];
        }

        return array_filter(
            $item->getChildren(),
            fn (MenuItem $child): bool => $this->isMenuItemGranted($child)
        );
    }

    /**
     * Check if a submenu has any granted children.
     */
    public function hasGrantedChildren(MenuItem $item): bool
    {
        return [] !== $this->getGrantedChildren($item);
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
