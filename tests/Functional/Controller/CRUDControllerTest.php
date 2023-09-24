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

final class CRUDControllerTest extends WebTestCase
{
    public function testList(): void
    {
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/admin/tests/app/foo/list');

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        static::assertCount(
            1,
            $crawler->filter('.sonata-ba-list-field:contains("foo_name")')
        );
    }

    public function testCreate(): void
    {
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/admin/tests/app/foo/create');

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        static::assertCount(
            1,
            $crawler->filter('.sonata-ba-collapsed-fields label:contains("Name")')
        );
        static::assertCount(
            1,
            $crawler->filter('.help-block.sonata-ba-field-help:contains("Help me!")')
        );
    }

    /**
     * https://github.com/sonata-project/SonataAdminBundle/issues/6904.
     */
    public function testCreateModelAutoCompleteNotPassingSubclassParameter(): void
    {
        $subclass = uniqid('subclass');
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/admin/tests/app/foo/create?subclass='.$subclass);

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        static::assertStringNotContainsString(
            $subclass,
            $crawler->filter('div[id$=_referenced]')->text(),
            'The subclass parameter must no be present in referenced model auto complete ajax call'
        );
    }

    public function testShow(): void
    {
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/admin/tests/app/foo/test_id/show');

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        static::assertCount(
            1,
            $crawler->filter('td:contains("foo_name")')
        );
    }

    public function testEdit(): void
    {
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/admin/tests/app/foo/test_id/edit');

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        static::assertCount(
            1,
            $crawler->filter('.sonata-ba-collapsed-fields label:contains("Name")')
        );
    }

    /**
     * @dataProvider provideUrlIsSuccessfulCases
     */
    public function testUrlIsSuccessful(string $url): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, $url);

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testBatchAction(): void
    {
        $client = static::createClient();

        $crawler = $client->request(Request::METHOD_GET, '/admin/tests/app/foo/list');

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $csrfToken = $crawler->selectButton('OK')->form()->getValues()['_sonata_csrf_token'];

        $client->request(
            Request::METHOD_POST,
            '/admin/tests/app/foo/batch',
            [
                'data' => json_encode(['action' => 'other', 'all_elements' => true]),
                '_sonata_csrf_token' => $csrfToken,
            ]
        );

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        static::assertSame('Other Controller', $client->getResponse()->getContent());
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public function provideUrlIsSuccessfulCases(): iterable
    {
        yield ['/admin/tests/app/foo/browse'];
        // CustomAdminExtension route
        yield ['/admin/empty/list'];
        yield ['/admin/empty/create'];
        yield ['/admin/empty/test_id/show'];
        yield ['/admin/empty/test_id/edit'];
        yield ['/admin/tests/app/foo-with-custom-controller/list'];
        yield ['/admin/tests/app/foo-with-custom-controller/create'];
        yield ['/admin/tests/app/foo-with-custom-controller/test_id/show'];
        yield ['/admin/tests/app/foo-with-custom-controller/test_id/edit'];
        yield ['/admin/tests/app/foo/test_id/bar/list'];
        yield ['/admin/tests/app/bar/test_id/baz/list'];
    }
}
