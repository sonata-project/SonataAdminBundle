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

final class MenuItem
{
    /**
     * @param string[] $roles
     * @param MenuItem[] $children
     */
    private function __construct(
        private string $type,
        private string $label,
        private ?string $icon = null,
        private ?string $url = null,
        private ?string $routeName = null,
        private array $routeParameters = [],
        private ?string $adminCode = null,
        private array $roles = [],
        private array $children = [],
    ) {
    }

    /**
     * @param string[] $roles Roles required to view this menu item
     */
    public static function linkToDashboard(string $label, ?string $icon = null, array $roles = []): self
    {
        return new self(
            type: 'dashboard',
            label: $label,
            icon: $icon,
            routeName: 'sensiolabs_admin_dashboard',
            roles: $roles,
        );
    }

    /**
     * @param string[] $roles Roles required to view this menu item
     */
    public static function linkToCrud(string $label, ?string $icon = null, ?string $adminCode = null, array $roles = []): self
    {
        return new self(
            type: 'crud',
            label: $label,
            icon: $icon,
            adminCode: $adminCode,
            roles: $roles,
        );
    }

    /**
     * @param string[] $roles Roles required to view this menu item
     */
    public static function linkToRoute(string $label, ?string $icon = null, string $routeName = '', array $routeParameters = [], array $roles = []): self
    {
        return new self(
            type: 'route',
            label: $label,
            icon: $icon,
            routeName: $routeName,
            routeParameters: $routeParameters,
            roles: $roles,
        );
    }

    /**
     * @param string[] $roles Roles required to view this menu item
     */
    public static function linkToUrl(string $label, ?string $icon = null, string $url = '', array $roles = []): self
    {
        return new self(
            type: 'url',
            label: $label,
            icon: $icon,
            url: $url,
            roles: $roles,
        );
    }

    /**
     * @param string[] $roles Roles required to view this menu item
     */
    public static function section(string $label, ?string $icon = null, array $roles = []): self
    {
        return new self(
            type: 'section',
            label: $label,
            icon: $icon,
            roles: $roles,
        );
    }

    /**
     * @param string[] $roles Roles required to view this menu item
     */
    public static function subMenu(string $label, ?string $icon = null, array $roles = [], MenuItem ...$children): self
    {
        return new self(
            type: 'submenu',
            label: $label,
            icon: $icon,
            roles: $roles,
            children: $children,
        );
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    public function getAdminCode(): ?string
    {
        return $this->adminCode;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return MenuItem[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function isSection(): bool
    {
        return 'section' === $this->type;
    }

    public function isSubMenu(): bool
    {
        return 'submenu' === $this->type;
    }
}
