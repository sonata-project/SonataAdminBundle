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

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AdminAsParameterControllerTest extends WebTestCase
{
    protected function tearDown(): void
    {
        restore_exception_handler();

        parent::tearDown();
    }

    #[DataProvider('provideUrlIsSuccessfulCases')]
    public function testUrlIsSuccessful(string $url): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, $url);

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public static function provideUrlIsSuccessfulCases(): iterable
    {
        yield ['/admin/tests/app/admin-as-parameter/test?uniqid=test'];
        yield ['/admin/tests/app/admin-as-parameter/invokable?uniqid=invokable'];
    }
}
