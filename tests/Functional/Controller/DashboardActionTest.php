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
}
