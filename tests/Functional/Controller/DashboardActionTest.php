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

namespace SensioLabs\AdminBundle\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DashboardActionTest extends WebTestCase
{
    protected function tearDown(): void
    {
        restore_exception_handler();

        parent::tearDown();
    }

    public function testDashboard(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/admin/dashboard');

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testDashboardRendersSidebar(): void
    {
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/admin/dashboard');

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Sidebar should exist
        static::assertGreaterThan(0, $crawler->filter('.admin-sidebar')->count());

        // Navigation container should exist
        static::assertGreaterThan(0, $crawler->filter('.admin-sidebar-nav')->count());
    }

    public function testDashboardRendersMenuItems(): void
    {
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/admin/dashboard');

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Menu items should be present (dashboard link + admin CRUD items)
        $menuItems = $crawler->filter('.admin-sidebar-item');
        static::assertGreaterThan(0, $menuItems->count(), 'Dashboard should render at least one menu item');
    }

    public function testDashboardContainsDashboardLink(): void
    {
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/admin/dashboard');

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Should contain a link to the dashboard
        $dashboardLinks = $crawler->filter('.admin-sidebar-item[href="/admin/dashboard"]');
        static::assertGreaterThan(0, $dashboardLinks->count(), 'Dashboard should contain a link back to dashboard');
    }

    public function testDashboardMenuItemsHaveLabels(): void
    {
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/admin/dashboard');

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Each menu item should have a label
        $labels = $crawler->filter('.admin-sidebar-item-label');
        static::assertGreaterThan(0, $labels->count(), 'Menu items should have labels');

        // Labels should not be empty
        $labels->each(static function ($node): void {
            static::assertNotEmpty(trim($node->text()), 'Menu item label should not be empty');
        });
    }
}
