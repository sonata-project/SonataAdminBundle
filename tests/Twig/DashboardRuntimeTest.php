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

namespace SensioLabs\AdminBundle\Tests\Twig;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Dashboard\DashboardControllerInterface;
use SensioLabs\AdminBundle\Dashboard\MenuItem;
use SensioLabs\AdminBundle\Twig\DashboardRuntime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class DashboardRuntimeTest extends TestCase
{
    private MockObject&DashboardControllerInterface $dashboardController;
    private MockObject&ContainerInterface $container;
    private MockObject&UrlGeneratorInterface $urlGenerator;
    private MockObject&AuthorizationCheckerInterface $authorizationChecker;

    protected function setUp(): void
    {
        $this->dashboardController = $this->createMock(DashboardControllerInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
    }

    private function createRuntime(array $adminServiceCodes = []): DashboardRuntime
    {
        $pool = new Pool($this->container, $adminServiceCodes);

        return new DashboardRuntime(
            $this->dashboardController,
            $pool,
            $this->urlGenerator,
            $this->authorizationChecker,
        );
    }

    public function testGetMenuItems(): void
    {
        $items = [
            MenuItem::linkToDashboard('Home'),
            MenuItem::linkToCrud('Users', null, 'app.admin.user'),
        ];

        $this->dashboardController->method('getMenuItems')->willReturn($items);

        $runtime = $this->createRuntime();

        static::assertSame($items, $runtime->getMenuItems());
    }

    public function testIsMenuItemGrantedForCrudWithAdminAccess(): void
    {
        $item = MenuItem::linkToCrud('Users', null, 'app.admin.user');

        $admin = $this->createMock(AdminInterface::class);
        $admin->method('hasRoute')->with('list')->willReturn(true);
        $admin->method('showInDashboard')->willReturn(true);

        $this->container->method('get')->with('app.admin.user')->willReturn($admin);

        $runtime = $this->createRuntime(['app.admin.user']);

        static::assertTrue($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemGrantedForCrudWithNoListRoute(): void
    {
        $item = MenuItem::linkToCrud('Users', null, 'app.admin.user');

        $admin = $this->createMock(AdminInterface::class);
        $admin->method('hasRoute')->with('list')->willReturn(false);

        $this->container->method('get')->with('app.admin.user')->willReturn($admin);

        $runtime = $this->createRuntime(['app.admin.user']);

        static::assertFalse($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemGrantedForCrudNotShownInDashboard(): void
    {
        $item = MenuItem::linkToCrud('Users', null, 'app.admin.user');

        $admin = $this->createMock(AdminInterface::class);
        $admin->method('hasRoute')->with('list')->willReturn(true);
        $admin->method('showInDashboard')->willReturn(false);

        $this->container->method('get')->with('app.admin.user')->willReturn($admin);

        $runtime = $this->createRuntime(['app.admin.user']);

        static::assertFalse($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemGrantedForCrudWithNullAdminCode(): void
    {
        $item = MenuItem::linkToCrud('Label');

        $runtime = $this->createRuntime();

        static::assertTrue($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemGrantedForCrudWithUnknownAdminCode(): void
    {
        $item = MenuItem::linkToCrud('Users', null, 'app.admin.nonexistent');

        // Pool will throw AdminCodeNotFoundException for unknown codes
        $runtime = $this->createRuntime([]);

        static::assertTrue($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemGrantedForCrudWithRoles(): void
    {
        $item = MenuItem::linkToCrud('Users', null, 'app.admin.user', ['ROLE_SUPER_ADMIN']);

        $admin = $this->createMock(AdminInterface::class);
        $admin->method('hasRoute')->with('list')->willReturn(true);
        $admin->method('showInDashboard')->willReturn(true);

        $this->container->method('get')->with('app.admin.user')->willReturn($admin);
        $this->authorizationChecker->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(true);

        $runtime = $this->createRuntime(['app.admin.user']);

        static::assertTrue($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemDeniedForCrudWithMissingRole(): void
    {
        $item = MenuItem::linkToCrud('Users', null, 'app.admin.user', ['ROLE_SUPER_ADMIN']);

        $admin = $this->createMock(AdminInterface::class);
        $admin->method('hasRoute')->with('list')->willReturn(true);
        $admin->method('showInDashboard')->willReturn(true);

        $this->container->method('get')->with('app.admin.user')->willReturn($admin);
        $this->authorizationChecker->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(false);

        $runtime = $this->createRuntime(['app.admin.user']);

        static::assertFalse($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemGrantedForRouteWithNoRoles(): void
    {
        $item = MenuItem::linkToRoute('Report', null, 'app_report');

        $runtime = $this->createRuntime();

        static::assertTrue($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemGrantedForRouteWithGrantedRoles(): void
    {
        $item = MenuItem::linkToRoute('Report', null, 'app_report', [], ['ROLE_ADMIN']);

        $this->authorizationChecker->method('isGranted')->with('ROLE_ADMIN')->willReturn(true);

        $runtime = $this->createRuntime();

        static::assertTrue($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemDeniedForRouteWithMissingRole(): void
    {
        $item = MenuItem::linkToRoute('Report', null, 'app_report', [], ['ROLE_ADMIN']);

        $this->authorizationChecker->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);

        $runtime = $this->createRuntime();

        static::assertFalse($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemGrantedForUrlWithNoRoles(): void
    {
        $item = MenuItem::linkToUrl('Docs', null, 'https://example.com');

        $runtime = $this->createRuntime();

        static::assertTrue($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemGrantedForUrlWithRoles(): void
    {
        $item = MenuItem::linkToUrl('Docs', null, 'https://example.com', ['ROLE_EDITOR']);

        $this->authorizationChecker->method('isGranted')->with('ROLE_EDITOR')->willReturn(true);

        $runtime = $this->createRuntime();

        static::assertTrue($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemDeniedForUrlWithMissingRole(): void
    {
        $item = MenuItem::linkToUrl('Docs', null, 'https://example.com', ['ROLE_EDITOR']);

        $this->authorizationChecker->method('isGranted')->with('ROLE_EDITOR')->willReturn(false);

        $runtime = $this->createRuntime();

        static::assertFalse($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemGrantedForDashboardWithNoRoles(): void
    {
        $item = MenuItem::linkToDashboard('Home');

        $runtime = $this->createRuntime();

        static::assertTrue($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemGrantedForDashboardWithRoles(): void
    {
        $item = MenuItem::linkToDashboard('Home', null, ['ROLE_ADMIN']);

        $this->authorizationChecker->method('isGranted')->with('ROLE_ADMIN')->willReturn(true);

        $runtime = $this->createRuntime();

        static::assertTrue($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemDeniedForDashboardWithMissingRole(): void
    {
        $item = MenuItem::linkToDashboard('Home', null, ['ROLE_ADMIN']);

        $this->authorizationChecker->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);

        $runtime = $this->createRuntime();

        static::assertFalse($runtime->isMenuItemGranted($item));
    }

    public function testGetGrantedChildrenFiltersCorrectly(): void
    {
        $grantedChild = MenuItem::linkToRoute('Public', null, 'public_route');
        $deniedChild = MenuItem::linkToRoute('Secret', null, 'secret_route', [], ['ROLE_SUPER_ADMIN']);

        $subMenu = MenuItem::subMenu('Menu', null, [], $grantedChild, $deniedChild);

        $this->authorizationChecker->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(false);

        $runtime = $this->createRuntime();

        $grantedChildren = $runtime->getGrantedChildren($subMenu);

        static::assertCount(1, $grantedChildren);
        static::assertSame('Public', array_values($grantedChildren)[0]->getLabel());
    }

    public function testGetGrantedChildrenReturnsEmptyForNonSubMenu(): void
    {
        $item = MenuItem::linkToDashboard('Home');

        $runtime = $this->createRuntime();

        static::assertSame([], $runtime->getGrantedChildren($item));
    }

    public function testHasGrantedChildrenReturnsTrueWhenChildrenExist(): void
    {
        $child = MenuItem::linkToRoute('Public', null, 'public_route');
        $subMenu = MenuItem::subMenu('Menu', null, [], $child);

        $runtime = $this->createRuntime();

        static::assertTrue($runtime->hasGrantedChildren($subMenu));
    }

    public function testHasGrantedChildrenReturnsFalseWhenAllDenied(): void
    {
        $child = MenuItem::linkToRoute('Secret', null, 'secret_route', [], ['ROLE_SUPER_ADMIN']);
        $subMenu = MenuItem::subMenu('Menu', null, [], $child);

        $this->authorizationChecker->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(false);

        $runtime = $this->createRuntime();

        static::assertFalse($runtime->hasGrantedChildren($subMenu));
    }

    public function testHasGrantedChildrenReturnsFalseForNonSubMenu(): void
    {
        $item = MenuItem::section('Section');

        $runtime = $this->createRuntime();

        static::assertFalse($runtime->hasGrantedChildren($item));
    }

    public function testGetMenuItemUrlForDashboard(): void
    {
        $item = MenuItem::linkToDashboard('Home');

        $this->urlGenerator->method('generate')
            ->with('sensiolabs_admin_dashboard')
            ->willReturn('/admin/dashboard');

        $runtime = $this->createRuntime();

        static::assertSame('/admin/dashboard', $runtime->getMenuItemUrl($item));
    }

    public function testGetMenuItemUrlForCrud(): void
    {
        $item = MenuItem::linkToCrud('Users', null, 'app.admin.user');

        $admin = $this->createMock(AdminInterface::class);
        $admin->method('generateUrl')->with('list')->willReturn('/admin/user/list');

        $this->container->method('get')->with('app.admin.user')->willReturn($admin);

        $runtime = $this->createRuntime(['app.admin.user']);

        static::assertSame('/admin/user/list', $runtime->getMenuItemUrl($item));
    }

    public function testGetMenuItemUrlForCrudWithNullAdminCode(): void
    {
        $item = MenuItem::linkToCrud('Label');

        $runtime = $this->createRuntime();

        static::assertNull($runtime->getMenuItemUrl($item));
    }

    public function testGetMenuItemUrlForCrudWithUnknownAdmin(): void
    {
        $item = MenuItem::linkToCrud('Users', null, 'nonexistent');

        // Pool will throw for unknown admin codes
        $runtime = $this->createRuntime([]);

        static::assertNull($runtime->getMenuItemUrl($item));
    }

    public function testGetMenuItemUrlForRoute(): void
    {
        $item = MenuItem::linkToRoute('Report', null, 'app_report', ['year' => '2025']);

        $this->urlGenerator->method('generate')
            ->with('app_report', ['year' => '2025'])
            ->willReturn('/report?year=2025');

        $runtime = $this->createRuntime();

        static::assertSame('/report?year=2025', $runtime->getMenuItemUrl($item));
    }

    public function testGetMenuItemUrlForUrl(): void
    {
        $item = MenuItem::linkToUrl('Docs', null, 'https://example.com');

        $runtime = $this->createRuntime();

        static::assertSame('https://example.com', $runtime->getMenuItemUrl($item));
    }

    public function testGetMenuItemUrlForSection(): void
    {
        $item = MenuItem::section('Content');

        $runtime = $this->createRuntime();

        static::assertNull($runtime->getMenuItemUrl($item));
    }

    public function testGetMenuItemUrlForSubMenu(): void
    {
        $item = MenuItem::subMenu('Menu');

        $runtime = $this->createRuntime();

        static::assertNull($runtime->getMenuItemUrl($item));
    }

    public function testIsMenuItemGrantedForSectionWithNoRoles(): void
    {
        $item = MenuItem::section('Content');

        $runtime = $this->createRuntime();

        static::assertTrue($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemGrantedForSectionWithRoles(): void
    {
        $item = MenuItem::section('Admin Section', null, ['ROLE_ADMIN']);

        $this->authorizationChecker->method('isGranted')->with('ROLE_ADMIN')->willReturn(true);

        $runtime = $this->createRuntime();

        static::assertTrue($runtime->isMenuItemGranted($item));
    }

    public function testIsMenuItemDeniedForSectionWithMissingRole(): void
    {
        $item = MenuItem::section('Admin Section', null, ['ROLE_ADMIN']);

        $this->authorizationChecker->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);

        $runtime = $this->createRuntime();

        static::assertFalse($runtime->isMenuItemGranted($item));
    }
}
