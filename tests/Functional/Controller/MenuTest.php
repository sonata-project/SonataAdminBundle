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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MenuTest extends WebTestCase
{
    public function testDynamicMenuInLongRunningProcess(): void
    {
        $client = static::createClient();
        $client->disableReboot(); // forces requests to land at same Kernel, simulating long-running process like one used in roadrunner/reactphp/amphp

        for ($i = 1; $i < 5; ++$i) {
            $client->request(Request::METHOD_GET, '/admin/dashboard');

            static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

            $crawler = $client->getCrawler();

            $menu = $crawler->filter('.sidebar-menu .dynamic-menu a');

            static::assertCount(1, $menu);
            static::assertSame(\sprintf('Dynamic Menu %s', $i), trim($menu->text()));
        }
    }
}
