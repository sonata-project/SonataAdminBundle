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

namespace SensioLabs\AdminBundle\Tests\Menu\Matcher\Voter;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Menu\Matcher\Voter\AdminVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class AdminVoterTest extends TestCase
{
    #[DataProvider('provideMatchingCases')]
    public function testMatching(mixed $itemData, ?string $voterData, ?string $route, ?bool $expected): void
    {
        $item = $this->createMock(ItemInterface::class);
        $item
            ->method('getExtra')
            ->with(static::logicalOr(
                static::equalTo('admin'),
                static::equalTo('route')
            ))
            ->willReturn($itemData);

        $request = new Request();
        $request->request->set('_sonata_admin', $voterData);
        $request->request->set('_route', $route);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $voter = new AdminVoter($requestStack);

        static::assertSame($expected, $voter->matchItem($item));
    }

    /**
     * @return iterable<array{mixed, string|null, string|null, bool|null}>
     */
    public static function provideMatchingCases(): iterable
    {
        yield 'no data' => [null, null, null, null];
        yield 'no route and granted' => [static::getAdmin('_sonata_admin'), '_sonata_admin', null, null];
        yield 'no granted' => [static::getAdmin('_sonata_admin', true), '_sonata_admin', null, null];
        yield 'no code' => [static::getAdmin('_sonata_admin_code', true, true), '_sonata_admin', null, null];
        yield 'no code request' => [static::getAdmin('_sonata_admin', true, true), '_sonata_admin_unexpected', null, null];
        yield 'no route' => [static::getAdmin('_sonata_admin', false, true), '_sonata_admin', null, null];
        yield 'has admin' => [static::getAdmin('_sonata_admin', true, true), '_sonata_admin', null, true];
        yield 'has child admin' => [static::getChildAdmin('_sonata_admin', '_sonata_child_admin', true, true), '_sonata_admin|_sonata_child_admin', null, true];
        yield 'has bad child admin' => [static::getChildAdmin('_sonata_admin', '_sonata_child_admin', true, true), '_sonata_admin|_sonata_child_admin_unexpected', null, null];
        yield 'has nested child admin' => [static::getNestedChildAdmin('_sonata_admin', '_sonata_child_admin', '_sonata_nested_child_admin', true, true), '_sonata_admin|_sonata_child_admin|_sonata_nested_child_admin', null, true];
        yield 'has bad nested child admin' => [static::getNestedChildAdmin('_sonata_admin', '_sonata_child_admin', '_sonata_nested_child_admin', true, true), '_sonata_admin|_sonata_child_admin|_sonata_nested_child_admin_unexpected', null, null];
        yield 'direct link' => ['admin_post', null, 'admin_post', true];
        yield 'no direct link' => ['admin_post', null, 'admin_blog', null];
    }

    /**
     * @return AdminInterface<object>
     */
    private static function getAdmin(string $code, bool $list = false, bool $granted = false): AdminInterface
    {
        $admin = static::createStub(AdminInterface::class);
        $admin->method('hasRoute')->willReturn($list);
        $admin->method('hasAccess')->willReturn($granted);
        $admin->method('getBaseCodeRoute')->willReturn($code);
        $admin->method('getChildren')->willReturn([]);

        return $admin;
    }

    /**
     * @return AdminInterface<object>
     */
    private static function getChildAdmin(
        string $parentCode,
        string $childCode,
        bool $list = false,
        bool $granted = false,
    ): AdminInterface {
        $parentAdmin = static::createStub(AdminInterface::class);
        $parentAdmin->method('hasRoute')->willReturn($list);
        $parentAdmin->method('hasAccess')->willReturn($granted);
        $parentAdmin->method('getBaseCodeRoute')->willReturn($parentCode);

        $childAdmin = static::createStub(AdminInterface::class);
        $childAdmin->method('getBaseCodeRoute')->willReturn(\sprintf('%s|%s', $parentCode, $childCode));

        $parentAdmin->method('getChildren')->willReturn([$childAdmin]);

        return $parentAdmin;
    }

    /**
     * @return AdminInterface<object>
     */
    private static function getNestedChildAdmin(
        string $grandParentCode,
        string $parentCode,
        string $childCode,
        bool $list = false,
        bool $granted = false,
    ): AdminInterface {
        $grandParentAdmin = static::createStub(AdminInterface::class);
        $grandParentAdmin->method('hasRoute')->willReturn($list);
        $grandParentAdmin->method('hasAccess')->willReturn($granted);
        $grandParentAdmin->method('getBaseCodeRoute')->willReturn($grandParentCode);

        $parentAdmin = static::createStub(AdminInterface::class);
        $parentAdmin->method('getBaseCodeRoute')->willReturn(\sprintf('%s|%s', $grandParentCode, $parentCode));

        $grandParentAdmin->method('getChildren')->willReturn([$parentAdmin]);

        $childAdmin = static::createStub(AdminInterface::class);
        $childAdmin->method('getBaseCodeRoute')->willReturn(\sprintf('%s|%s|%s', $grandParentCode, $parentCode, $childCode));

        $parentAdmin->method('getChildren')->willReturn([$childAdmin]);

        return $grandParentAdmin;
    }
}
