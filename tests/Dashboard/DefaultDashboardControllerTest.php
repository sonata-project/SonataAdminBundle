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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Dashboard\DefaultDashboardController;
use SensioLabs\AdminBundle\Templating\TemplateRegistryInterface;
use Twig\Environment;

final class DefaultDashboardControllerTest extends TestCase
{
    private MockObject&ContainerInterface $container;
    private DefaultDashboardController $controller;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $templateRegistry = $this->createMock(TemplateRegistryInterface::class);
        $twig = $this->createMock(Environment::class);

        $this->controller = new DefaultDashboardController();
        $this->controller->setTemplateRegistry($templateRegistry);
        $this->controller->setTwig($twig);
    }

    private function createPool(array $adminServiceCodes = []): Pool
    {
        return new Pool($this->container, $adminServiceCodes);
    }

    public function testConfigureMenuItemsYieldsDashboardLink(): void
    {
        $this->controller->setPool($this->createPool());

        $items = iterator_to_array($this->controller->configureMenuItems());

        static::assertCount(1, $items);
        static::assertSame('dashboard', $items[0]->getType());
        static::assertSame('Dashboard', $items[0]->getLabel());
        static::assertSame('lucide:home', $items[0]->getIcon());
    }

    public function testConfigureMenuItemsGeneratesItemsFromAdmins(): void
    {
        $admin1 = $this->createMock(AdminInterface::class);
        $admin1->method('isChild')->willReturn(false);
        $admin1->method('getLabel')->willReturn('Users');
        $admin1->method('getCode')->willReturn('app.admin.user');

        $admin2 = $this->createMock(AdminInterface::class);
        $admin2->method('isChild')->willReturn(false);
        $admin2->method('getLabel')->willReturn('Posts');
        $admin2->method('getCode')->willReturn('app.admin.post');

        $this->container->method('get')->willReturnCallback(
            static fn (string $code) => match ($code) {
                'app.admin.user' => $admin1,
                'app.admin.post' => $admin2,
                default => throw new \RuntimeException('Unexpected admin code: '.$code),
            }
        );

        $this->controller->setPool($this->createPool(['app.admin.user', 'app.admin.post']));

        $items = iterator_to_array($this->controller->configureMenuItems());

        static::assertCount(3, $items);
        // First item is always the dashboard link
        static::assertSame('dashboard', $items[0]->getType());
        // Then the admin items
        static::assertSame('crud', $items[1]->getType());
        static::assertSame('Users', $items[1]->getLabel());
        static::assertSame('app.admin.user', $items[1]->getAdminCode());
        static::assertSame('crud', $items[2]->getType());
        static::assertSame('Posts', $items[2]->getLabel());
        static::assertSame('app.admin.post', $items[2]->getAdminCode());
    }

    public function testConfigureMenuItemsSkipsChildAdmins(): void
    {
        $parentAdmin = $this->createMock(AdminInterface::class);
        $parentAdmin->method('isChild')->willReturn(false);
        $parentAdmin->method('getLabel')->willReturn('Categories');
        $parentAdmin->method('getCode')->willReturn('app.admin.category');

        $childAdmin = $this->createMock(AdminInterface::class);
        $childAdmin->method('isChild')->willReturn(true);

        $this->container->method('get')->willReturnCallback(
            static fn (string $code) => match ($code) {
                'app.admin.category' => $parentAdmin,
                'app.admin.category_item' => $childAdmin,
                default => throw new \RuntimeException('Unexpected admin code: '.$code),
            }
        );

        $this->controller->setPool($this->createPool(['app.admin.category', 'app.admin.category_item']));

        $items = iterator_to_array($this->controller->configureMenuItems());

        // Dashboard + 1 parent admin (child admin is skipped)
        static::assertCount(2, $items);
        static::assertSame('dashboard', $items[0]->getType());
        static::assertSame('Categories', $items[1]->getLabel());
    }

    public function testConfigureMenuItemsUsesAdminCodeWhenLabelIsNull(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $admin->method('isChild')->willReturn(false);
        $admin->method('getLabel')->willReturn(null);
        $admin->method('getCode')->willReturn('app.admin.unlabeled');

        $this->container->method('get')->with('app.admin.unlabeled')->willReturn($admin);

        $this->controller->setPool($this->createPool(['app.admin.unlabeled']));

        $items = iterator_to_array($this->controller->configureMenuItems());

        static::assertSame('app.admin.unlabeled', $items[1]->getLabel());
    }

    public function testConfigureDashboard(): void
    {
        $this->controller->setPool($this->createPool());

        $config = $this->controller->configureDashboard();

        static::assertArrayHasKey('title', $config);
        static::assertSame('Administration', $config['title']);
    }

    public function testGetMenuItemsConvertsIterableToArray(): void
    {
        $this->controller->setPool($this->createPool());

        $items = $this->controller->getMenuItems();

        static::assertIsArray($items);
        static::assertCount(1, $items);
    }
}
