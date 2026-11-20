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

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Runs the history actions of the bar child admin through the kernel with the role security
 * handler. In the "role_security" environment, the bar "locked" belongs to foo "test_id" but its
 * history is denied by an object based voter, and the bar "orphan" does not belong to any foo.
 */
final class HistoryAccessTest extends WebTestCase
{
    protected function tearDown(): void
    {
        restore_exception_handler();

        parent::tearDown();
    }

    #[DataProvider('provideActionsOfTheOwnChildCases')]
    public function testActionsOfTheOwnChild(string $url): void
    {
        $client = self::createClientAs('bar_auditor');
        $client->request(Request::METHOD_GET, $url);

        self::assertStatusCode(Response::HTTP_OK, $client);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideActionsOfTheOwnChildCases(): iterable
    {
        yield 'history' => ['/admin/tests/app/foo/test_id/bar/test_id/history'];
        yield 'history view revision' => ['/admin/tests/app/foo/test_id/bar/test_id/history/1/view'];
        yield 'history compare revisions' => ['/admin/tests/app/foo/test_id/bar/test_id/history/1/2/compare'];
    }

    #[DataProvider('provideActionsOfAChildOfAnotherParentAreRejectedCases')]
    public function testActionsOfAChildOfAnotherParentAreRejected(string $url): void
    {
        $client = self::createClientAs('bar_auditor');
        $client->catchExceptions(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('There is no association between');

        $client->request(Request::METHOD_GET, $url);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideActionsOfAChildOfAnotherParentAreRejectedCases(): iterable
    {
        yield 'history' => ['/admin/tests/app/foo/test_id/bar/orphan/history'];
        yield 'history view revision' => ['/admin/tests/app/foo/test_id/bar/orphan/history/1/view'];
        yield 'history compare revisions' => ['/admin/tests/app/foo/test_id/bar/orphan/history/1/2/compare'];
    }

    #[DataProvider('provideActionsOfALockedObjectAreDeniedCases')]
    public function testActionsOfALockedObjectAreDenied(string $url): void
    {
        $client = self::createClientAs('bar_auditor');
        $client->request(Request::METHOD_GET, $url);

        self::assertStatusCode(Response::HTTP_FORBIDDEN, $client);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideActionsOfALockedObjectAreDeniedCases(): iterable
    {
        yield 'history' => ['/admin/tests/app/foo/test_id/bar/locked/history'];
        yield 'history view revision' => ['/admin/tests/app/foo/test_id/bar/locked/history/1/view'];
        yield 'history compare revisions' => ['/admin/tests/app/foo/test_id/bar/locked/history/1/2/compare'];
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
