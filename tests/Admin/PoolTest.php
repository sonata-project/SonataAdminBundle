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

namespace Sonata\AdminBundle\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Exception\AdminCodeNotFoundException;
use Sonata\AdminBundle\Exception\TooManyAdminClassException;
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
     *
     * @group legacy
     */
    public function testGetDashboardGroupsForLegacyAdmin(): void
    {
        $adminGroup1 = $this->createMock(AdminInterface::class);
        $adminGroup1->expects(static::once())->method('showIn')->willReturn(true);

        $adminGroup2 = $this->createMock(AdminInterface::class);
        $adminGroup2->expects(static::once())->method('showIn')->willReturn(false);

        $adminGroup3 = $this->createMock(AdminInterface::class);
        $adminGroup3->expects(static::once())->method('showIn')->willReturn(false);

        $this->container->set('sonata.user.admin.group1', $adminGroup1);
        $this->container->set('sonata.user.admin.group2', $adminGroup2);
        $this->container->set('sonata.user.admin.group3', $adminGroup3);

        $pool = new Pool(
            $this->container,
            ['sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3'],
            [
                'adminGroup1' => $this->getGroupArray('sonata.user.admin.group1'),
                'adminGroup2' => $this->getGroupArray('sonata.user.admin.group2'),
                'adminGroup3' => $this->getGroupArray('sonata.user.admin.group3'),
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
        $adminGroup1 = $this->getMockBuilder(AdminInterface::class)->addMethods(['showInDashboard'])->getMockForAbstractClass();
        $adminGroup1->expects(static::once())->method('showInDashboard')->willReturn(true);

        // NEXT_MAJOR: Use $this->createMock(AdminInterface::class);
        $adminGroup2 = $this->getMockBuilder(AdminInterface::class)->addMethods(['showInDashboard'])->getMockForAbstractClass();
        $adminGroup2->expects(static::once())->method('showInDashboard')->willReturn(false);

        // NEXT_MAJOR: Use $this->createMock(AdminInterface::class);
        $adminGroup3 = $this->getMockBuilder(AdminInterface::class)->addMethods(['showInDashboard'])->getMockForAbstractClass();
        $adminGroup3->expects(static::once())->method('showInDashboard')->willReturn(false);

        $this->container->set('sonata.user.admin.group1', $adminGroup1);
        $this->container->set('sonata.user.admin.group2', $adminGroup2);
        $this->container->set('sonata.user.admin.group3', $adminGroup3);

        $pool = new Pool(
            $this->container,
            ['sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3'],
            [
                'adminGroup1' => $this->getGroupArray('sonata.user.admin.group1'),
                'adminGroup2' => $this->getGroupArray('sonata.user.admin.group2'),
                'adminGroup3' => $this->getGroupArray('sonata.user.admin.group3'),
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

        $pool = new Pool($this->container, ['sonata.user.admin.group1'], [], [
            $class => ['sonata.user.admin.group1', 'sonata.user.admin.group2'],
        ]);

        static::assertTrue($pool->hasAdminByClass($class));

        $this->expectException(TooManyAdminClassException::class);

        $pool->getAdminByClass($class);
    }

    public function testGetAdminForClassWithTooManyRegisteredAdminButOneDefaultAdmin(): void
    {
        $class = \stdClass::class;

        $this->container->set('sonata.user.admin.group1', $this->createMock(AdminInterface::class));

        $pool = new Pool($this->container, ['sonata.user.admin.group1'], [], [
            $class => [Pool::DEFAULT_ADMIN_KEY => 'sonata.user.admin.group1', 'sonata.user.admin.group2'],
        ]);

        static::assertTrue($pool->hasAdminByClass($class));
        static::assertInstanceOf(AdminInterface::class, $pool->getAdminByClass($class));
    }

    public function testGetAdminForClassWhenAdminClassIsSet(): void
    {
        $class = \stdClass::class;

        $this->container->set('sonata.user.admin.group1', $this->createMock(AdminInterface::class));

        $pool = new Pool($this->container, ['sonata.user.admin.group1'], [], [$class => ['sonata.user.admin.group1']]);

        static::assertTrue($pool->hasAdminByClass($class));
        static::assertInstanceOf(AdminInterface::class, $pool->getAdminByClass($class));
    }

    public function testGetInstanceWithUndefinedServiceId(): void
    {
        $this->expectException(AdminCodeNotFoundException::class);
        $this->expectExceptionMessage('Admin service "sonata.news.admin.post" not found in admin pool.');

        $this->pool->getInstance('sonata.news.admin.post');
    }

    public function testGetInstanceWithUndefinedServiceIdAndExistsOther(): void
    {
        $pool = new Pool($this->container, [
            'sonata.news.admin.post',
            'sonata.news.admin.category',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Admin service "sonata.news.admin.pos" not found in admin pool. Did you mean "sonata.news.admin.post" or one of those: [sonata.news.admin.category]?');

        $pool->getInstance('sonata.news.admin.pos');
    }

    public function testGetAdminByAdminCode(): void
    {
        $this->container->set('sonata.news.admin.post', $this->createMock(AdminInterface::class));

        $pool = new Pool($this->container, ['sonata.news.admin.post']);

        static::assertInstanceOf(AdminInterface::class, $pool->getAdminByAdminCode('sonata.news.admin.post'));
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
            ->with(static::equalTo('sonata.news.admin.comment'))
            ->willReturn($childAdmin);

        $this->container->set('sonata.news.admin.post', $adminMock);

        $pool = new Pool($this->container, ['sonata.news.admin.post', 'sonata.news.admin.comment']);

        static::assertSame($childAdmin, $pool->getAdminByAdminCode('sonata.news.admin.post|sonata.news.admin.comment'));
    }

    public function testGetAdminByAdminCodeWithInvalidCode(): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock
            ->method('hasChild')
            ->willReturn(false);

        $this->container->set('sonata.news.admin.post', $adminMock);
        $pool = new Pool($this->container, ['sonata.news.admin.post']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument 1 passed to Sonata\AdminBundle\Admin\Pool::getAdminByAdminCode() must contain a valid admin reference, "sonata.news.admin.invalid" found at "sonata.news.admin.post|sonata.news.admin.invalid".');

        $pool->getAdminByAdminCode('sonata.news.admin.post|sonata.news.admin.invalid');
    }

    public function testGetAdminByAdminCodeWithCodeNotChild(): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock
            ->method('hasChild')
            ->willReturn(false);
        $adminMock
            ->method('getCode')
            ->willReturn('sonata.news.admin.post');

        $this->container->set('sonata.news.admin.post', $adminMock);
        $pool = new Pool($this->container, ['sonata.news.admin.post', 'sonata.news.admin.valid']);

        $this->expectException(AdminCodeNotFoundException::class);
        $this->expectExceptionMessage('Argument 1 passed to Sonata\AdminBundle\Admin\Pool::getAdminByAdminCode() must contain a valid admin hierarchy, "sonata.news.admin.valid" is not a valid child for "sonata.news.admin.post"');

        $pool->getAdminByAdminCode('sonata.news.admin.post|sonata.news.admin.valid');
    }

    /**
     * @dataProvider provideGetAdminByAdminCodeWithInvalidRootCodeCases
     */
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
    public function provideGetAdminByAdminCodeWithInvalidRootCodeCases(): iterable
    {
        yield [''];
        yield ['   '];
        yield ['|sonata.news.admin.child_of_empty_code'];
    }

    /**
     * @dataProvider provideGetAdminByAdminCodeWithInvalidChildCodeCases
     */
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
    public function provideGetAdminByAdminCodeWithInvalidChildCodeCases(): iterable
    {
        yield ['admin1|'];
        yield ['admin1|nonexistent_code'];
        yield ['admin1||admin3'];
    }

    /**
     * @dataProvider provideHasAdminByAdminCodeCases
     */
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
                ->with(static::equalTo('sonata.news.admin.comment'))
                ->willReturn($childAdminMock);
        } else {
            $adminMock->expects(static::never())
                ->method('hasChild');
            $adminMock->expects(static::never())
                ->method('getChild');
        }

        $this->container->set('sonata.news.admin.post', $adminMock);

        $pool = new Pool($this->container, ['sonata.news.admin.post', 'sonata.news.admin.comment']);

        static::assertTrue($pool->hasAdminByAdminCode($adminId));
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public function provideHasAdminByAdminCodeCases(): iterable
    {
        yield ['sonata.news.admin.post'];
        yield ['sonata.news.admin.post|sonata.news.admin.comment'];
    }

    /**
     * @dataProvider provideHasAdminByAdminCodeWithInvalidCodesCases
     */
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
    public function provideHasAdminByAdminCodeWithInvalidCodesCases(): iterable
    {
        yield [''];
        yield ['   '];
        yield ['|sonata.news.admin.child_of_empty_code'];
    }

    public function testHasAdminByAdminCodeWithNonExistentCode(): void
    {
        static::assertFalse($this->pool->hasAdminByAdminCode('sonata.news.admin.nonexistent_code'));
    }

    /**
     * @dataProvider provideHasAdminByAdminCodeWithInvalidChildCodesCases
     */
    public function testHasAdminByAdminCodeWithInvalidChildCodes(string $adminId): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock
            ->method('hasChild')
            ->willReturn(false);
        $adminMock->expects(static::never())
            ->method('getChild');

        $this->container->set('sonata.news.admin.post', $adminMock);

        static::assertFalse($this->pool->hasAdminByAdminCode($adminId));
    }

    /**
     * @phpstan-return iterable<array-key, array{string}>
     */
    public function provideHasAdminByAdminCodeWithInvalidChildCodesCases(): iterable
    {
        yield ['sonata.news.admin.post|'];
        yield ['sonata.news.admin.post|nonexistent_code'];
        yield ['sonata.news.admin.post||admin3'];
    }

    public function testGetAdminClasses(): void
    {
        $class = \stdClass::class;

        $pool = new Pool($this->container, [], [], [$class => ['sonata.user.admin.group1']]);
        static::assertSame([$class => ['sonata.user.admin.group1']], $pool->getAdminClasses());
    }

    public function testGetAdminGroups(): void
    {
        $groups = [
            'sonata.user.admin.group1' => [
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
        $pool = new Pool($this->container, ['sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3']);
        static::assertSame(['sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3'], $pool->getAdminServiceCodes());
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
