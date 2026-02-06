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

namespace SensioLabs\AdminBundle\Tests\Dashboard;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Dashboard\MenuItem;

final class MenuItemTest extends TestCase
{
    public function testLinkToDashboard(): void
    {
        $item = MenuItem::linkToDashboard('Home', 'lucide:home');

        static::assertSame('dashboard', $item->getType());
        static::assertSame('Home', $item->getLabel());
        static::assertSame('lucide:home', $item->getIcon());
        static::assertSame('sensiolabs_admin_dashboard', $item->getRouteName());
        static::assertSame([], $item->getRoles());
        static::assertNull($item->getAdminCode());
        static::assertNull($item->getUrl());
        static::assertSame([], $item->getRouteParameters());
        static::assertSame([], $item->getChildren());
        static::assertFalse($item->isSection());
        static::assertFalse($item->isSubMenu());
    }

    public function testLinkToDashboardWithRoles(): void
    {
        $item = MenuItem::linkToDashboard('Home', 'lucide:home', ['ROLE_ADMIN']);

        static::assertSame(['ROLE_ADMIN'], $item->getRoles());
    }

    public function testLinkToCrud(): void
    {
        $item = MenuItem::linkToCrud('Users', 'lucide:users', 'app.admin.user');

        static::assertSame('crud', $item->getType());
        static::assertSame('Users', $item->getLabel());
        static::assertSame('lucide:users', $item->getIcon());
        static::assertSame('app.admin.user', $item->getAdminCode());
        static::assertSame([], $item->getRoles());
        static::assertFalse($item->isSection());
        static::assertFalse($item->isSubMenu());
    }

    public function testLinkToCrudWithRoles(): void
    {
        $item = MenuItem::linkToCrud('Users', 'lucide:users', 'app.admin.user', ['ROLE_SUPER_ADMIN']);

        static::assertSame(['ROLE_SUPER_ADMIN'], $item->getRoles());
    }

    public function testLinkToCrudWithNullAdminCode(): void
    {
        $item = MenuItem::linkToCrud('Label');

        static::assertNull($item->getAdminCode());
    }

    public function testLinkToRoute(): void
    {
        $item = MenuItem::linkToRoute('Report', 'lucide:chart', 'app_report', ['year' => '2025']);

        static::assertSame('route', $item->getType());
        static::assertSame('Report', $item->getLabel());
        static::assertSame('lucide:chart', $item->getIcon());
        static::assertSame('app_report', $item->getRouteName());
        static::assertSame(['year' => '2025'], $item->getRouteParameters());
        static::assertSame([], $item->getRoles());
    }

    public function testLinkToRouteWithRoles(): void
    {
        $item = MenuItem::linkToRoute('Report', 'lucide:chart', 'app_report', [], ['ROLE_ADMIN']);

        static::assertSame(['ROLE_ADMIN'], $item->getRoles());
    }

    public function testLinkToUrl(): void
    {
        $item = MenuItem::linkToUrl('Docs', 'lucide:book', 'https://example.com');

        static::assertSame('url', $item->getType());
        static::assertSame('Docs', $item->getLabel());
        static::assertSame('lucide:book', $item->getIcon());
        static::assertSame('https://example.com', $item->getUrl());
        static::assertSame([], $item->getRoles());
    }

    public function testLinkToUrlWithRoles(): void
    {
        $item = MenuItem::linkToUrl('Docs', null, 'https://example.com', ['ROLE_EDITOR']);

        static::assertSame(['ROLE_EDITOR'], $item->getRoles());
    }

    public function testSection(): void
    {
        $item = MenuItem::section('Content', 'lucide:file');

        static::assertSame('section', $item->getType());
        static::assertSame('Content', $item->getLabel());
        static::assertSame('lucide:file', $item->getIcon());
        static::assertTrue($item->isSection());
        static::assertFalse($item->isSubMenu());
        static::assertSame([], $item->getRoles());
    }

    public function testSectionWithRoles(): void
    {
        $item = MenuItem::section('Admin Section', null, ['ROLE_ADMIN']);

        static::assertSame(['ROLE_ADMIN'], $item->getRoles());
    }

    public function testSubMenu(): void
    {
        $child1 = MenuItem::linkToCrud('Users', null, 'app.admin.user');
        $child2 = MenuItem::linkToCrud('Posts', null, 'app.admin.post');

        $item = MenuItem::subMenu('Management', 'lucide:settings', [], $child1, $child2);

        static::assertSame('submenu', $item->getType());
        static::assertSame('Management', $item->getLabel());
        static::assertSame('lucide:settings', $item->getIcon());
        static::assertFalse($item->isSection());
        static::assertTrue($item->isSubMenu());
        static::assertCount(2, $item->getChildren());
        static::assertSame($child1, $item->getChildren()[0]);
        static::assertSame($child2, $item->getChildren()[1]);
        static::assertSame([], $item->getRoles());
    }

    public function testSubMenuWithRoles(): void
    {
        $item = MenuItem::subMenu('Admin', null, ['ROLE_SUPER_ADMIN']);

        static::assertSame(['ROLE_SUPER_ADMIN'], $item->getRoles());
        static::assertSame([], $item->getChildren());
    }

    public function testSubMenuWithNoChildren(): void
    {
        $item = MenuItem::subMenu('Empty Menu');

        static::assertTrue($item->isSubMenu());
        static::assertSame([], $item->getChildren());
    }

    public function testNullIconIsAllowed(): void
    {
        $item = MenuItem::linkToDashboard('Home');

        static::assertNull($item->getIcon());
    }
}
