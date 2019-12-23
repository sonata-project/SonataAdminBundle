<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Functional\Controller;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Client;

final class DashboardActionTest extends TestCase
{
    public function testDashboard(): void
    {
        $client = new Client(new AppKernel());
        $client->request('GET', '/admin/dashboard');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }
}
