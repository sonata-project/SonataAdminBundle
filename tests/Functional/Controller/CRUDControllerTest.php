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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CRUDControllerTest extends WebTestCase
{
    public function testList(): void
    {
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/admin/tests/app/foo/list');

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('.sonata-ba-list-field:contains("foo_name")')
        );
    }

    public function testCreate(): void
    {
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/admin/tests/app/foo/create');

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('.sonata-ba-collapsed-fields label:contains("Name")')
        );
        $this->assertCount(
            1,
            $crawler->filter('p.help-block.sonata-ba-field-help:contains("Help me!")')
        );
    }

    public function testShow(): void
    {
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/admin/tests/app/foo/test_id/show');

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('td:contains("foo_name")')
        );
    }

    public function testEdit(): void
    {
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/admin/tests/app/foo/test_id/edit');

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertCount(
            1,
            $crawler->filter('.sonata-ba-collapsed-fields label:contains("Name")')
        );
    }

    /**
     * @dataProvider urlIsSuccessfulDataProvider
     */
    public function testUrlIsSuccessful(string $url): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, $url);

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    /**
     * @phpstan-return iterable<array{string}>
     */
    public function urlIsSuccessfulDataProvider(): iterable
    {
        return [
            ['/admin/empty/list'],
            ['/admin/empty/create'],
            ['/admin/empty/test_id/show'],
            ['/admin/empty/test_id/edit'],
            ['/admin/tests/app/foo-with-custom-controller/list'],
            ['/admin/tests/app/foo-with-custom-controller/create'],
            ['/admin/tests/app/foo-with-custom-controller/test_id/show'],
            ['/admin/tests/app/foo-with-custom-controller/test_id/edit'],
        ];
    }

    protected static function getKernelClass()
    {
        return AppKernel::class;
    }
}
