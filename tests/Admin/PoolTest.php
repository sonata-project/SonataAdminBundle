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

namespace SensioLabs\AdminBundle\Tests\Admin;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Exception\AdminCodeNotFoundException;
use SensioLabs\AdminBundle\Exception\TooManyAdminClassException;
use Symfony\Component\DependencyInjection\Container;

/**
 * @phpstan-import-type Group from Pool
 */
final class PoolTest extends TestCase
{
    private Container $container;

    private Pool $pool;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->pool = new Pool($this->container);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     */
    #[IgnoreDeprecations]
    public function testGetDashboardGroupsForLegacyAdmin(): void
    {
        $adminGroup1 = $this->createMock(AdminInterface::class);
        $adminGroup1->expects(static::once())->method('showIn')->willReturn(true);

        $adminGroup2 = $this->createMock(AdminInterface::class);
        $adminGroup2->expects(static::once())->method('showIn')->willReturn(false);

        $adminGroup3 = $this->createMock(AdminInterface::class);
        $adminGroup3->expects(static::once())->method('showIn')->willReturn(false);

        $this->container->set('sensiolabs.user.admin.group1', $adminGroup1);
        $this->container->set('sensiolabs.user.admin.group2', $adminGroup2);
        $this->container->set('sensiolabs.user.admin.group3', $adminGroup3);

        $pool = new Pool(
            $this->container,
            ['sensiolabs.user.admin.group1', 'sensiolabs.user.admin.group2', 'sensiolabs.user.admin.group3'],
            [
                'adminGroup1' => $this->getGroupArray('sensiolabs.user.admin.group1'),
                'adminGroup2' => $this->getGroupArray('sensiolabs.user.admin.group2'),
                'adminGroup3' => $this->getGroupArray('sensiolabs.user.admin.group3'),
                'adminGroup4' => $this->getGroupArray(),
            ]
        );

        $groups = $pool->getDashboardGroups();

        static::assertCount(1, $groups);
        static::assertSame($adminGroup1, $groups['adminGroup1']['items'][0]);
    }

    public function testGetDashboardGroups(): void
    {
        // NEXT_MAJOR: Use $this->createMock(AdminInterface::class);
        $adminGroup1 = $this->createMock(NextMajorAdminInterface::class);
        $adminGroup1->expects(static::once())->method('showInDashboard')->willReturn(true);

        // NEXT_MAJOR: Use $this->createMock(AdminInterface::class);
        $adminGroup2 = $this->createMock(NextMajorAdminInterface::class);
        $adminGroup2->expects(static::once())->method('showInDashboard')->willReturn(false);

        // NEXT_MAJOR: Use $this->createMock(AdminInterface::class);
        $adminGroup3 = $this->createMock(NextMajorAdminInterface::class);
        $adminGroup3->expects(static::once())->method('showInDashboard')->willReturn(false);

        $this->container->set('sensiolabs.user.admin.group1', $adminGroup1);
        $this->container->set('sensiolabs.user.admin.group2', $adminGroup2);
        $this->container->set('sensiolabs.user.admin.group3', $adminGroup3);

        $pool = new Pool(
            $this->container,
            ['sensiolabs.user.admin.group1', 'sensiolabs.user.admin.group2', 'sensiolabs.user.admin.group3'],
            [
                'adminGroup1' => $this->getGroupArray('sensiolabs.user.admin.group1'),
                'adminGroup2' => $this->getGroupArray('sensiolabs.user.admin.group2'),
                'adminGroup3' => $this->getGroupArray('sensiolabs.user.admin.group3'),
                'adminGroup4' => $this->getGroupArray(),
            ]
        );

        $groups = $pool->getDashboardGroups();

        static::assertCount(1, $groups);
        static::assertSame($adminGroup1, $groups['adminGroup1']['items'][0]);
    }

    public function testGetAdminForClassWithTooManyRegisteredAdmin(): void
    {
        $class = \stdClass::class;

        $pool = new Pool($this->container, ['sensiolabs.user.admin.group1'], [], [
            $class => ['sensiolabs.user.admin.group1', 'sensiolabs.user.admin.group2'],
        ]);

        static::assertTrue($pool->hasAdminByClass($class));

        $this->expectException(TooManyAdminClassException::class);

        $pool->getAdminByClass($class);
    }

    public function testGetAdminForClassWithTooManyRegisteredAdminButOneDefaultAdmin(): void
    {
        $class = \stdClass::class;

        $this->container->set('sensiolabs.user.admin.group1', $this->createMock(AdminInterface::class));

        $pool = new Pool($this->container, ['sensiolabs.user.admin.group1'], [], [
            $class => [Pool::DEFAULT_ADMIN_KEY => 'sensiolabs.user.admin.group1', 'sensiolabs.user.admin.group2'],
        ]);

        static::assertTrue($pool->hasAdminByClass($class));
        static::assertInstanceOf(AdminInterface::class, $pool->getAdminByClass($class));
    }

    public function testGetAdminForClassWhenAdminClassIsSet(): void
    {
        $class = \stdClass::class;

        $this->container->set('sensiolabs.user.admin.group1', $this->createMock(AdminInterface::class));

        $pool = new Pool($this->container, ['sensiolabs.user.admin.group1'], [], [$class => ['sensiolabs.user.admin.group1']]);

        static::assertTrue($pool->hasAdminByClass($class));
        static::assertInstanceOf(AdminInterface::class, $pool->getAdminByClass($class));
    }

    public function testGetInstanceWithUndefinedServiceId(): void
    {
        $this->expectException(AdminCodeNotFoundException::class);
        $this->expectExceptionMessage('Admin service "sensiolabs.news.admin.post" not found in admin pool.');

        $this->pool->getInstance('sensiolabs.news.admin.post');
    }

    public function testGetInstanceWithUndefinedServiceIdAndExistsOther(): void
    {
        $pool = new Pool($this->container, [
            'sensiolabs.news.admin.post',
            'sensiolabs.news.admin.category',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Admin service "sensiolabs.news.admin.pos" not found in admin pool. Did you mean "sensiolabs.news.admin.post" or one of those: [sensiolabs.news.admin.category]?');

        $pool->getInstance('sensiolabs.news.admin.pos');
    }

    public function testGetAdminByAdminCode(): void
    {
        $this->container->set('sensiolabs.news.admin.post', $this->createMock(AdminInterface::class));

        $pool = new Pool($this->container, ['sensiolabs.news.admin.post']);

        static::assertInstanceOf(AdminInterface::class, $pool->getAdminByAdminCode('sensiolabs.news.admin.post'));
    }

    public function testGetAdminByAdminCodeForChildClass(): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock
            ->method('hasChild')
            ->willReturn(true);

        $childAdmin = $this->createMock(AdminInterface::class);

        $adminMock->expects(static::once())
            ->method('getChild')
            ->with(static::equalTo('sensiolabs.news.admin.comment'))
            ->willReturn($childAdmin);

        $this->container->set('sensiolabs.news.admin.post', $adminMock);

        $pool = new Pool($this->container, ['sensiolabs.news.admin.post', 'sensiolabs.news.admin.comment']);

        static::assertSame($childAdmin, $pool->getAdminByAdminCode('sensiolabs.news.admin.post|sensiolabs.news.admin.comment'));
    }

    public function testGetAdminByAdminCodeWithInvalidCode(): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock
            ->method('hasChild')
            ->willReturn(false);

        $this->container->set('sensiolabs.news.admin.post', $adminMock);
        $pool = new Pool($this->container, ['sensiolabs.news.admin.post']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument 1 passed to SensioLabs\AdminBundle\Admin\Pool::getAdminByAdminCode() must contain a valid admin reference, "sensiolabs.news.admin.invalid" found at "sensiolabs.news.admin.post|sensiolabs.news.admin.invalid".');

        $pool->getAdminByAdminCode('sensiolabs.news.admin.post|sensiolabs.news.admin.invalid');
    }

    public function testGetAdminByAdminCodeWithCodeNotChild(): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock
            ->method('hasChild')
            ->willReturn(false);
        $adminMock
            ->method('getCode')
            ->willReturn('sensiolabs.news.admin.post');

        $this->container->set('sensiolabs.news.admin.post', $adminMock);
        $pool = new Pool($this->container, ['sensiolabs.news.admin.post', 'sensiolabs.news.admin.valid']);

        $this->expectException(AdminCodeNotFoundException::class);
        $this->expectExceptionMessage('Argument 1 passed to SensioLabs\AdminBundle\Admin\Pool::getAdminByAdminCode() must contain a valid admin hierarchy, "sensiolabs.news.admin.valid" is not a valid child for "sensiolabs.news.admin.post"');

        $pool->getAdminByAdminCode('sensiolabs.news.admin.post|sensiolabs.news.admin.valid');
    }

    #[DataProvider('provideGetAdminByAdminCodeWithInvalidRootCodeCases')]
    public function testGetAdminByAdminCodeWithInvalidRootCode(string $adminId): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock->expects(static::never())
            ->method('hasChild');

        $pool = new Pool($this->container, [$adminId]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Admin code must contain a valid admin reference, empty string given.');
        $pool->getAdminByAdminCode($adminId);
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public static function provideGetAdminByAdminCodeWithInvalidRootCodeCases(): iterable
    {
        yield [''];
        yield ['   '];
        yield ['|sensiolabs.news.admin.child_of_empty_code'];
    }

    #[DataProvider('provideGetAdminByAdminCodeWithInvalidChildCodeCases')]
    public function testGetAdminByAdminCodeWithInvalidChildCode(string $adminId): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock
            ->method('hasChild')
            ->willReturn(false);
        $adminMock->expects(static::never())
            ->method('getChild');

        $this->container->set('admin1', $adminMock);
        $pool = new Pool($this->container, ['admin1']);

        $this->expectException(AdminCodeNotFoundException::class);
        $this->expectExceptionMessageMatches(\sprintf(
            '{^Argument 1 passed to Sonata\\\AdminBundle\\\Admin\\\Pool::getAdminByAdminCode\(\) must contain a valid admin reference, "[^"]+" found at "%s"\.$}',
            $adminId
        ));

        $pool->getAdminByAdminCode($adminId);
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public static function provideGetAdminByAdminCodeWithInvalidChildCodeCases(): iterable
    {
        yield ['admin1|'];
        yield ['admin1|nonexistent_code'];
        yield ['admin1||admin3'];
    }

    #[DataProvider('provideHasAdminByAdminCodeCases')]
    public function testHasAdminByAdminCode(string $adminId): void
    {
        $adminMock = $this->createMock(AdminInterface::class);

        if (str_contains($adminId, '|')) {
            $childAdminMock = $this->createMock(AdminInterface::class);
            $adminMock
                ->method('hasChild')
                ->willReturn(true);
            $adminMock->expects(static::once())
                ->method('getChild')
                ->with(static::equalTo('sensiolabs.news.admin.comment'))
                ->willReturn($childAdminMock);
        } else {
            $adminMock->expects(static::never())
                ->method('hasChild');
            $adminMock->expects(static::never())
                ->method('getChild');
        }

        $this->container->set('sensiolabs.news.admin.post', $adminMock);

        $pool = new Pool($this->container, ['sensiolabs.news.admin.post', 'sensiolabs.news.admin.comment']);

        static::assertTrue($pool->hasAdminByAdminCode($adminId));
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public static function provideHasAdminByAdminCodeCases(): iterable
    {
        yield ['sensiolabs.news.admin.post'];
        yield ['sensiolabs.news.admin.post|sensiolabs.news.admin.comment'];
    }

    #[DataProvider('provideHasAdminByAdminCodeWithInvalidCodesCases')]
    public function testHasAdminByAdminCodeWithInvalidCodes(string $adminId): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock
            ->method('hasChild')
            ->willReturn(false);
        $adminMock->expects(static::never())
            ->method('getChild');

        static::assertFalse($this->pool->hasAdminByAdminCode($adminId));
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public static function provideHasAdminByAdminCodeWithInvalidCodesCases(): iterable
    {
        yield [''];
        yield ['   '];
        yield ['|sensiolabs.news.admin.child_of_empty_code'];
    }

    public function testHasAdminByAdminCodeWithNonExistentCode(): void
    {
        static::assertFalse($this->pool->hasAdminByAdminCode('sensiolabs.news.admin.nonexistent_code'));
    }

    #[DataProvider('provideHasAdminByAdminCodeWithInvalidChildCodesCases')]
    public function testHasAdminByAdminCodeWithInvalidChildCodes(string $adminId): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock
            ->method('hasChild')
            ->willReturn(false);
        $adminMock->expects(static::never())
            ->method('getChild');

        $this->container->set('sensiolabs.news.admin.post', $adminMock);

        static::assertFalse($this->pool->hasAdminByAdminCode($adminId));
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public static function provideHasAdminByAdminCodeWithInvalidChildCodesCases(): iterable
    {
        yield ['sensiolabs.news.admin.post|'];
        yield ['sensiolabs.news.admin.post|nonexistent_code'];
        yield ['sensiolabs.news.admin.post||admin3'];
    }

    public function testGetAdminClasses(): void
    {
        $class = \stdClass::class;

        $pool = new Pool($this->container, [], [], [$class => ['sensiolabs.user.admin.group1']]);
        static::assertSame([$class => ['sensiolabs.user.admin.group1']], $pool->getAdminClasses());
    }

    public function testGetAdminGroups(): void
    {
        $groups = [
            'sensiolabs.user.admin.group1' => [
                'label' => 'label',
                'icon' => 'icon',
                'translation_domain' => 'admin_domain',
                'items' => [],
                'keep_open' => false,
                'on_top' => false,
                'roles' => [],
            ],
        ];

        $pool = new Pool($this->container, [], $groups);
        static::assertSame($groups, $pool->getAdminGroups());
    }

    public function testGetAdminServiceCodes(): void
    {
        $pool = new Pool($this->container, ['sensiolabs.user.admin.group1', 'sensiolabs.user.admin.group2', 'sensiolabs.user.admin.group3']);
        static::assertSame(['sensiolabs.user.admin.group1', 'sensiolabs.user.admin.group2', 'sensiolabs.user.admin.group3'], $pool->getAdminServiceCodes());
    }

    /**
     * @phpstan-return Group
     */
    private function getGroupArray(?string $serviceId = null): array
    {
        $item = [
            'label' => '',
            'route' => '',
            'route_absolute' => false,
            'route_params' => [],
            'roles' => [],
        ];

        if (null !== $serviceId) {
            $item['admin'] = $serviceId;
        }

        return [
            'label' => '',
            'translation_domain' => '',
            'icon' => '',
            'items' => [$item],
            'keep_open' => false,
            'on_top' => false,
            'roles' => [],
        ];
    }
}
