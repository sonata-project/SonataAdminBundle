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

namespace Sonata\AdminBundle\Tests\Functional\Action;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Runs the helper actions through the kernel with the role security handler, see the
 * "role_security" environment of the test application for the users and their permissions.
 */
final class HelperActionAccessTest extends WebTestCase
{
    private const UNIQID = 's1a2b3c4d5e6f7';

    protected function tearDown(): void
    {
        restore_exception_handler();

        parent::tearDown();
    }

    public function testShortObjectDescriptionOfAnotherAdminIsDenied(): void
    {
        $client = self::createClientAs('foo_editor');
        $client->request(Request::METHOD_GET, '/admin/core/get-short-object-description.json', [
            '_sonata_admin' => 'sonata_bar_admin',
            'objectId' => 'test_id',
        ]);

        self::assertStatusCode(Response::HTTP_FORBIDDEN, $client);
    }

    public function testShortObjectDescriptionWithListAccessOnlyIsDenied(): void
    {
        $client = self::createClientAs('bar_lister');
        $client->request(Request::METHOD_GET, '/admin/core/get-short-object-description.json', [
            '_sonata_admin' => 'sonata_bar_admin',
            'objectId' => 'test_id',
        ]);

        self::assertStatusCode(Response::HTTP_FORBIDDEN, $client);
    }

    public function testShortObjectDescriptionWithShowAccess(): void
    {
        $client = self::createClientAs('bar_viewer');
        $client->request(Request::METHOD_GET, '/admin/core/get-short-object-description.json', [
            '_sonata_admin' => 'sonata_bar_admin',
            'objectId' => 'test_id',
        ]);

        self::assertStatusCode(Response::HTTP_OK, $client);
        static::assertStringContainsString('"id":"test_id"', (string) $client->getResponse()->getContent());
    }

    public function testFormFieldElementOfAnotherAdminIsDenied(): void
    {
        $client = self::createClientAs('bar_viewer');
        $client->request(Request::METHOD_GET, '/admin/core/get-form-field-element', [
            '_sonata_admin' => 'sonata_foo_admin',
            'objectId' => 'test_id',
            'uniqid' => self::UNIQID,
            'elementId' => self::UNIQID.'_name',
        ]);

        self::assertStatusCode(Response::HTTP_FORBIDDEN, $client);
    }

    public function testFormFieldElementWithEditAccess(): void
    {
        $client = self::createClientAs('foo_editor');
        $client->request(Request::METHOD_GET, '/admin/core/get-form-field-element', [
            '_sonata_admin' => 'sonata_foo_admin',
            'objectId' => 'test_id',
            'uniqid' => self::UNIQID,
            'elementId' => self::UNIQID.'_name',
        ]);

        self::assertStatusCode(Response::HTTP_OK, $client);
        static::assertStringContainsString('value="foo_name"', (string) $client->getResponse()->getContent());
    }

    public function testAppendFormFieldElementOfAnotherAdminIsDenied(): void
    {
        $client = self::createClientAs('bar_viewer');
        $client->request(Request::METHOD_POST, '/admin/core/append-form-field-element?'.http_build_query([
            '_sonata_admin' => 'sonata_foo_admin',
            'objectId' => 'test_id',
            'uniqid' => self::UNIQID,
            'elementId' => self::UNIQID.'_collection',
        ]));

        self::assertStatusCode(Response::HTTP_FORBIDDEN, $client);
    }

    private static function createClientAs(string $username): KernelBrowser
    {
        $client = static::createClient(['environment' => 'role_security']);

        $userProvider = static::getContainer()->get('security.user.provider.concrete.in_memory');
        Assert::assertInstanceOf(UserProviderInterface::class, $userProvider);

        $client->loginUser($userProvider->loadUserByIdentifier($username));

        return $client;
    }

    private static function assertStatusCode(int $expected, KernelBrowser $client): void
    {
        $response = $client->getResponse();

        static::assertSame(
            $expected,
            $response->getStatusCode(),
            \sprintf('Unexpected response: %s', substr(trim((string) preg_replace('/\s+/', ' ', (string) $response->getContent())), 0, 500))
        );
    }
}
