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

class PoolTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Pool
     */
    private $pool;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->pool = new Pool($this->container);
    }

    public function testGetDashboardGroups(): void
    {
        $adminGroup1 = $this->createMock(AdminInterface::class);
        $adminGroup1->expects($this->once())->method('showIn')->willReturn(true);

        $adminGroup2 = $this->createMock(AdminInterface::class);
        $adminGroup2->expects($this->once())->method('showIn')->willReturn(false);

        $adminGroup3 = $this->createMock(AdminInterface::class);
        $adminGroup3->expects($this->once())->method('showIn')->willReturn(false);

        $this->container->set('sonata.user.admin.group1', $adminGroup1);
        $this->container->set('sonata.user.admin.group2', $adminGroup2);
        $this->container->set('sonata.user.admin.group3', $adminGroup3);

        $pool = new Pool(
            $this->container,
            ['sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3'],
            [
                'adminGroup1' => [
                    'items' => ['itemKey' => $this->getItemArray('sonata.user.admin.group1')],
                ],
                'adminGroup2' => [
                    'items' => ['itemKey' => $this->getItemArray('sonata.user.admin.group2')],
                ],
                'adminGroup3' => [
                    'items' => ['itemKey' => $this->getItemArray('sonata.user.admin.group3')],
                ],
                'adminGroup4' => [
                    'items' => ['itemKey' => $this->getItemArray()],
                ],
            ]
        );

        $groups = $pool->getDashboardGroups();

        $this->assertCount(1, $groups);
        $this->assertSame($adminGroup1, $groups['adminGroup1']['items']['itemKey']);
    }

    public function testGetAdminForClassWithTooManyRegisteredAdmin(): void
    {
        /** @var class-string $class */
        $class = 'someclass';

        $pool = new Pool($this->container, ['sonata.user.admin.group1'], [], [
            $class => ['sonata.user.admin.group1', 'sonata.user.admin.group2'],
        ]);

        $this->assertTrue($pool->hasAdminByClass($class));

        $this->expectException(TooManyAdminClassException::class);

        $pool->getAdminByClass($class);
    }

    public function testGetAdminForClassWithTooManyRegisteredAdminButOneDefaultAdmin(): void
    {
        /** @var class-string $class */
        $class = 'someclass';

        $this->container->set('sonata.user.admin.group1', $this->createMock(AdminInterface::class));

        $pool = new Pool($this->container, ['sonata.user.admin.group1'], [], [
            $class => [Pool::DEFAULT_ADMIN_KEY => 'sonata.user.admin.group1', 'sonata.user.admin.group2'],
        ]);

        $this->assertTrue($pool->hasAdminByClass($class));
        $this->assertInstanceOf(AdminInterface::class, $pool->getAdminByClass($class));
    }

    public function testGetAdminForClassWhenAdminClassIsSet(): void
    {
        /** @var class-string $class */
        $class = 'someclass';

        $this->container->set('sonata.user.admin.group1', $this->createMock(AdminInterface::class));

        $pool = new Pool($this->container, ['sonata.user.admin.group1'], [], [$class => ['sonata.user.admin.group1']]);

        $this->assertTrue($pool->hasAdminByClass($class));
        $this->assertInstanceOf(AdminInterface::class, $pool->getAdminByClass($class));
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

        $this->assertInstanceOf(AdminInterface::class, $pool->getAdminByAdminCode('sonata.news.admin.post'));
    }

    public function testGetAdminByAdminCodeForChildClass(): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock
            ->method('hasChild')
            ->willReturn(true);

        $childAdmin = $this->createMock(AdminInterface::class);

        $adminMock->expects($this->once())
            ->method('getChild')
            ->with($this->equalTo('sonata.news.admin.comment'))
            ->willReturn($childAdmin);

        $this->container->set('sonata.news.admin.post', $adminMock);

        $pool = new Pool($this->container, ['sonata.news.admin.post', 'sonata.news.admin.comment']);

        $this->assertSame($childAdmin, $pool->getAdminByAdminCode('sonata.news.admin.post|sonata.news.admin.comment'));
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
     * @dataProvider getNonStringAdminServiceNames
     */
    public function testGetAdminByAdminCodeWithNonStringCode($adminId): void
    {
        $this->expectException(\TypeError::class);

        $this->pool->getAdminByAdminCode($adminId);
    }

    public function getNonStringAdminServiceNames(): array
    {
        return [
            [null],
            [false],
            [1],
            [['some_value']],
            [new \stdClass()],
        ];
    }

    /**
     * @dataProvider getEmptyRootAdminServiceNames
     */
    public function testGetAdminByAdminCodeWithInvalidRootCode(string $adminId): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock->expects($this->never())
            ->method('hasChild');

        $pool = new Pool($this->container, [$adminId]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Admin code must contain a valid admin reference, empty string given.');
        $pool->getAdminByAdminCode($adminId);
    }

    public function getEmptyRootAdminServiceNames()
    {
        return [
            [''],
            ['   '],
            ['|sonata.news.admin.child_of_empty_code'],
        ];
    }

    /**
     * @dataProvider getInvalidChildAdminServiceNames
     */
    public function testGetAdminByAdminCodeWithInvalidChildCode(string $adminId): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock
            ->method('hasChild')
            ->willReturn(false);
        $adminMock->expects($this->never())
            ->method('getChild');

        $this->container->set('admin1', $adminMock);
        $pool = new Pool($this->container, ['admin1']);

        $this->expectException(AdminCodeNotFoundException::class);
        $this->expectExceptionMessageMatches(sprintf(
            '{^Argument 1 passed to Sonata\\\AdminBundle\\\Admin\\\Pool::getAdminByAdminCode\(\) must contain a valid admin reference, "[^"]+" found at "%s"\.$}',
            $adminId
        ));

        $pool->getAdminByAdminCode($adminId);
    }

    public function getInvalidChildAdminServiceNames()
    {
        return [
            ['admin1|'],
            ['admin1|nonexistent_code'],
            ['admin1||admin3'],
        ];
    }

    /**
     * @dataProvider getAdminServiceNamesToCheck
     */
    public function testHasAdminByAdminCode(string $adminId): void
    {
        $adminMock = $this->createMock(AdminInterface::class);

        if (false !== strpos($adminId, '|')) {
            $childAdminMock = $this->createMock(AdminInterface::class);
            $adminMock
                ->method('hasChild')
                ->willReturn(true);
            $adminMock->expects($this->once())
                ->method('getChild')
                ->with($this->equalTo('sonata.news.admin.comment'))
                ->willReturn($childAdminMock);
        } else {
            $adminMock->expects($this->never())
                ->method('hasChild');
            $adminMock->expects($this->never())
                ->method('getChild');
        }

        $this->container->set('sonata.news.admin.post', $adminMock);

        $pool = new Pool($this->container, ['sonata.news.admin.post', 'sonata.news.admin.comment']);

        $this->assertTrue($pool->hasAdminByAdminCode($adminId));
    }

    public function getAdminServiceNamesToCheck()
    {
        return [
            ['sonata.news.admin.post'],
            ['sonata.news.admin.post|sonata.news.admin.comment'],
        ];
    }

    /**
     * @dataProvider getNonStringAdminServiceNames
     */
    public function testHasAdminByAdminCodeWithNonStringCode($adminId): void
    {
        $this->expectException(\TypeError::class);
        $this->pool->hasAdminByAdminCode($adminId);
    }

    /**
     * @dataProvider getInvalidAdminServiceNamesToCheck
     */
    public function testHasAdminByAdminCodeWithInvalidCodes(string $adminId): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock
            ->method('hasChild')
            ->willReturn(false);
        $adminMock->expects($this->never())
            ->method('getChild');

        $this->assertFalse($this->pool->hasAdminByAdminCode($adminId));
    }

    public function getInvalidAdminServiceNamesToCheck()
    {
        return [
            [''],
            ['   '],
            ['|sonata.news.admin.child_of_empty_code'],
        ];
    }

    public function testHasAdminByAdminCodeWithNonExistentCode(): void
    {
        $this->assertFalse($this->pool->hasAdminByAdminCode('sonata.news.admin.nonexistent_code'));
    }

    /**
     * @dataProvider getInvalidChildAdminServiceNamesToCheck
     */
    public function testHasAdminByAdminCodeWithInvalidChildCodes(string $adminId): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock
            ->method('hasChild')
            ->willReturn(false);
        $adminMock->expects($this->never())
            ->method('getChild');

        $this->container->set('sonata.news.admin.post', $adminMock);

        $this->assertFalse($this->pool->hasAdminByAdminCode($adminId));
    }

    public function getInvalidChildAdminServiceNamesToCheck(): array
    {
        return [
            ['sonata.news.admin.post|'],
            ['sonata.news.admin.post|nonexistent_code'],
            ['sonata.news.admin.post||admin3'],
        ];
    }

    public function testGetAdminClasses(): void
    {
        /** @var class-string $class */
        $class = 'someclass';

        $pool = new Pool($this->container, [], [], [$class => ['sonata.user.admin.group1']]);
        $this->assertSame([$class => ['sonata.user.admin.group1']], $pool->getAdminClasses());
    }

    public function testGetAdminGroups(): void
    {
        /** @var class-string $class */
        $class = 'someclass';

        $pool = new Pool($this->container, [], [$class => ['sonata.user.admin.group1' => []]]);
        $this->assertSame([$class => ['sonata.user.admin.group1' => []]], $pool->getAdminGroups());
    }

    public function testGetAdminServiceIds(): void
    {
        $pool = new Pool($this->container, ['sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3']);
        $this->assertSame(['sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3'], $pool->getAdminServiceIds());
    }

    private function getItemArray(?string $serviceId = null): array
    {
        $item = [
            'label' => '',
            'route' => '',
            'route_params' => [],
        ];

        if (null !== $serviceId) {
            $item['admin'] = $serviceId;
        }

        return $item;
    }
}
