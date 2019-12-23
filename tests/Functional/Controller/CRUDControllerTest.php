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

use Sonata\AdminBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CRUDControllerTest extends WebTestCase
{
    public function testList(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/tests/app/foo/list');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(
            1,
            $crawler->filter('.sonata-ba-list-field:contains("foo_name")')->count()
        );
    }

    public function testCreate(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/tests/app/foo/create');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(
            1,
            $crawler->filter('.sonata-ba-collapsed-fields label:contains("Name")')->count()
        );
    }

    public function testShow(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/tests/app/foo/test_id/show');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(
            1,
            $crawler->filter('td:contains("foo_name")')->count()
        );
    }

    public function testEdit(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/tests/app/foo/test_id/edit');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(
            1,
            $crawler->filter('.sonata-ba-collapsed-fields label:contains("Name")')->count()
        );
    }

    protected static function getKernelClass()
    {
        return AppKernel::class;
    }
}
