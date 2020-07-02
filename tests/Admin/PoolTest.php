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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        $this->pool = new Pool($this->container, 'Sonata Admin', '/path/to/pic.png', ['foo' => 'bar']);
    }

    public function testGetGroups(): void
    {
        $this->container->set('sonata.user.admin.group1', $this->createMock(AdminInterface::class));

        $this->pool->setAdminServiceIds(['sonata.user.admin.group1']);

        $this->pool->setAdminGroups([
            'adminGroup1' => ['sonata.user.admin.group1' => []],
        ]);

        $result = $this->pool->getGroups();
        $this->assertArrayHasKey('adminGroup1', $result);
        $this->assertArrayHasKey('sonata.user.admin.group1', $result['adminGroup1']);
    }

    public function testHasGroup(): void
    {
        $this->pool->setAdminGroups([
            'adminGroup1' => [],
        ]);

        $this->assertTrue($this->pool->hasGroup('adminGroup1'));
        $this->assertFalse($this->pool->hasGroup('adminGroup2'));
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

        $this->pool->setAdminServiceIds(['sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3']);

        $this->pool->setAdminGroups([
            'adminGroup1' => [
                'items' => ['itemKey' => $this->getItemArray('sonata.user.admin.group1')],
            ],
            'adminGroup2' => [
                'items' => ['itemKey' => $this->getItemArray('sonata.user.admin.group2')],
            ],
            'adminGroup3' => [
                'items' => ['itemKey' => $this->getItemArray('sonata.user.admin.group3')],
            ],
        ]);

        $groups = $this->pool->getDashboardGroups();

        $this->assertCount(1, $groups);
        $this->assertSame($adminGroup1, $groups['adminGroup1']['items']['itemKey']);
    }

    public function testGetAdminsByGroupWhenGroupNotSet(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->pool->setAdminGroups([
                'adminGroup1' => [],
            ]);

        $this->pool->getAdminsByGroup('adminGroup2');
    }

    public function testGetAdminsByGroupWhenGroupIsEmpty(): void
    {
        $this->pool->setAdminGroups([
                'adminGroup1' => [],
            ]);

        $this->assertSame([], $this->pool->getAdminsByGroup('adminGroup1'));
    }

    public function testGetAdminsByGroup(): void
    {
        $this->container->set('sonata.admin1', $this->createMock(AdminInterface::class));
        $this->container->set('sonata.admin2', $this->createMock(AdminInterface::class));
        $this->container->set('sonata.admin3', $this->createMock(AdminInterface::class));

        $this->pool->setAdminServiceIds(['sonata.admin1', 'sonata.admin2', 'sonata.admin3']);
        $this->pool->setAdminGroups([
            'adminGroup1' => [
                'items' => [
                    $this->getItemArray('sonata.admin1'),
                    $this->getItemArray('sonata.admin2'),
                ],
            ],
            'adminGroup2' => [
                'items' => [$this->getItemArray('sonata.admin3')],
            ],
        ]);

        $this->assertCount(2, $this->pool->getAdminsByGroup('adminGroup1'));
        $this->assertCount(1, $this->pool->getAdminsByGroup('adminGroup2'));
    }

    public function testGetAdminForClassWithInvalidFormat(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->pool->setAdminClasses(['someclass' => 'sonata.user.admin.group1']);
        $this->assertTrue($this->pool->hasAdminByClass('someclass'));

        $this->pool->getAdminByClass('someclass');
    }

    public function testGetAdminForClassWithTooManyRegisteredAdmin(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->pool->setAdminClasses([
            'someclass' => ['sonata.user.admin.group1', 'sonata.user.admin.group2'],
        ]);

        $this->assertTrue($this->pool->hasAdminByClass('someclass'));
        $this->pool->getAdminByClass('someclass');
    }

    public function testGetAdminForClassWhenAdminClassIsSet(): void
    {
        $this->container->set('sonata.user.admin.group1', $this->createMock(AdminInterface::class));

        $this->pool->setAdminServiceIds(['sonata.user.admin.group1']);
        $this->pool->setAdminClasses([
            'someclass' => ['sonata.user.admin.group1'],
        ]);

        $this->assertTrue($this->pool->hasAdminByClass('someclass'));
        $this->assertInstanceOf(AdminInterface::class, $this->pool->getAdminByClass('someclass'));
    }

    public function testGetInstanceWithUndefinedServiceId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Admin service "sonata.news.admin.post" not found in admin pool.');

        $this->pool->getInstance('sonata.news.admin.post');
    }

    public function testGetInstanceWithUndefinedServiceIdAndExistsOther(): void
    {
        $this->pool->setAdminServiceIds([
            'sonata.news.admin.post',
            'sonata.news.admin.category',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Admin service "sonata.news.admin.pos" not found in admin pool. Did you mean "sonata.news.admin.post" or one of those: [sonata.news.admin.category]?');

        $this->pool->getInstance('sonata.news.admin.pos');
    }

    public function testGetAdminByAdminCode(): void
    {
        $this->container->set('sonata.news.admin.post', $this->createMock(AdminInterface::class));

        $this->pool->setAdminServiceIds(['sonata.news.admin.post']);

        $this->assertInstanceOf(AdminInterface::class, $this->pool->getAdminByAdminCode('sonata.news.admin.post'));
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

        $this->pool->setAdminServiceIds(['sonata.news.admin.post', 'sonata.news.admin.comment']);

        $this->assertSame($childAdmin, $this->pool->getAdminByAdminCode('sonata.news.admin.post|sonata.news.admin.comment'));
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation Passing an invalid admin code as argument 1 for Sonata\AdminBundle\Admin\Pool::getAdminByAdminCode() is deprecated since sonata-project/admin-bundle 3.50 and will throw an exception in 4.0.
     */
    public function testGetAdminByAdminCodeWithInvalidCode(): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock
            ->method('hasChild')
            ->willReturn(false);

        $this->container->set('sonata.news.admin.post', $adminMock);
        $this->pool->setAdminServiceIds(['sonata.news.admin.post']);

        // NEXT_MAJOR: remove the assertion around getAdminByAdminCode(), remove the "@group" and "@expectedDeprecation" annotations, and uncomment the following line
        // $this->expectException(\InvalidArgumentException::class);
        $this->assertFalse($this->pool->getAdminByAdminCode('sonata.news.admin.post|sonata.news.admin.invalid'));
    }

    /**
     * @dataProvider getNonStringAdminServiceNames
     *
     * @group legacy
     *
     * @expectedDeprecation Passing a non string value as argument 1 for Sonata\AdminBundle\Admin\Pool::getAdminByAdminCode() is deprecated since sonata-project/admin-bundle 3.51 and will cause a TypeError in 4.0.
     */
    public function testGetAdminByAdminCodeWithNonStringCode($adminId): void
    {
        // NEXT_MAJOR: remove the assertion around getAdminByAdminCode(), remove the "@group" and "@expectedDeprecation" annotations, and uncomment the following line
        // $this->expectException(\TypeError::class);
        $this->assertFalse($this->pool->getAdminByAdminCode($adminId));
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
     * @group legacy
     *
     * @expectedDeprecation Passing an invalid admin hierarchy inside argument 1 for %s() is deprecated since sonata-project/admin-bundle 3.51 and will throw an exception in 4.0.
     */
    public function testGetAdminByAdminCodeWithCodeNotChild(): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock
            ->method('hasChild')
            ->willReturn(false);

        $this->container->set('sonata.news.admin.post', $adminMock);
        $this->pool->setAdminServiceIds(['sonata.news.admin.post', 'sonata.news.admin.valid']);
        $this->assertFalse($this->pool->getAdminByAdminCode('sonata.news.admin.post|sonata.news.admin.invalid'));

        // NEXT_MAJOR: remove the "@group" and "@expectedDeprecation" annotations, the previous assertion and uncomment the following lines
        // $this->expectException(\InvalidArgumentException::class);
        // $this->expectExceptionMessage('Argument 1 passed to Sonata\AdminBundle\Admin\Pool::getAdminByAdminCode() must contain a valid admin hierarchy, "sonata.news.admin.valid" is not a valid child for "sonata.news.admin.post"');
        //
        // $this->pool->getAdminByAdminCode('sonata.news.admin.post|sonata.news.admin.valid');
    }

    /**
     * @dataProvider getEmptyRootAdminServiceNames
     */
    public function testGetAdminByAdminCodeWithInvalidRootCode(string $adminId): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock->expects($this->never())
            ->method('hasChild');

        /** @var MockObject|Pool $poolMock */
        $poolMock = $this->getMockBuilder(Pool::class)
            ->setConstructorArgs([$this->container, 'Sonata', '/path/to/logo.png'])
            ->disableOriginalClone()
            ->setMethodsExcept(['getAdminByAdminCode'])
            ->getMock();
        $poolMock->expects($this->never())
            ->method('getInstance');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Root admin code must contain a valid admin reference, empty string given.');
        $poolMock->getAdminByAdminCode($adminId);
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
     *
     * @group legacy
     *
     * @expectedDeprecation Passing an invalid admin code as argument 1 for Sonata\AdminBundle\Admin\Pool::getAdminByAdminCode() is deprecated since sonata-project/admin-bundle 3.50 and will throw an exception in 4.0.
     */
    public function testGetAdminByAdminCodeWithInvalidChildCode(string $adminId): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock
            ->method('hasChild')
            ->willReturn(false);
        $adminMock->expects($this->never())
            ->method('getChild');

        /** @var MockObject|Pool $poolMock */
        $poolMock = $this->getMockBuilder(Pool::class)
            ->setConstructorArgs([$this->container, 'Sonata', '/path/to/logo.png'])
            ->disableOriginalClone()
            ->setMethodsExcept(['getAdminByAdminCode'])
            ->getMock();
        $poolMock
            ->method('getInstance')
            ->willReturn($adminMock);

        // NEXT_MAJOR: remove the assertion around getAdminByAdminCode(), remove the "@group" and "@expectedDeprecation" annotations, and uncomment the following line
        // $this->expectException(\InvalidArgumentException::class);
        $this->assertFalse($poolMock->getAdminByAdminCode($adminId));
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

        $this->pool->setAdminServiceIds(['sonata.news.admin.post', 'sonata.news.admin.comment']);

        $this->assertTrue($this->pool->hasAdminByAdminCode($adminId));
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
     *
     * @group legacy
     *
     * @expectedDeprecation Passing an invalid admin %s argument 1 for Sonata\AdminBundle\Admin\Pool::getAdminByAdminCode() is deprecated since sonata-project/admin-bundle 3.%s and will throw an exception in 4.0.
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

        $this->pool->setAdminServiceIds(['sonata.news.admin.post']);

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
        $this->pool->setAdminClasses(['someclass' => 'sonata.user.admin.group1']);
        $this->assertSame(['someclass' => 'sonata.user.admin.group1'], $this->pool->getAdminClasses());
    }

    public function testGetAdminGroups(): void
    {
        $this->pool->setAdminGroups(['adminGroup1' => 'sonata.user.admin.group1']);
        $this->assertSame(['adminGroup1' => 'sonata.user.admin.group1'], $this->pool->getAdminGroups());
    }

    public function testGetAdminServiceIds(): void
    {
        $this->pool->setAdminServiceIds(['sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3']);
        $this->assertSame(['sonata.user.admin.group1', 'sonata.user.admin.group2', 'sonata.user.admin.group3'], $this->pool->getAdminServiceIds());
    }

    public function testGetContainer(): void
    {
        $this->assertInstanceOf(ContainerInterface::class, $this->pool->getContainer());
    }

    /**
     * @group legacy
     */
    public function testTemplate(): void
    {
        $templateRegistry = $this->prophesize(MutableTemplateRegistryInterface::class);
        $templateRegistry->getTemplate('ajax')
            ->shouldBeCalledTimes(1)
            ->willReturn('Foo.html.twig');

        $this->pool->setTemplateRegistry($templateRegistry->reveal());

        $this->assertSame('Foo.html.twig', $this->pool->getTemplate('ajax'));
    }

    /**
     * @group legacy
     */
    public function testSetGetTemplates(): void
    {
        $templates = [
            'ajax' => 'Foo.html.twig',
            'layout' => 'Bar.html.twig',
        ];

        $templateRegistry = $this->prophesize(MutableTemplateRegistryInterface::class);
        $templateRegistry->setTemplates($templates)
            ->shouldBeCalledTimes(1);
        $templateRegistry->getTemplates()
            ->shouldBeCalledTimes(1)
            ->willReturn($templates);

        $this->pool->setTemplateRegistry($templateRegistry->reveal());

        $this->pool->setTemplates($templates);

        $this->assertSame($templates, $this->pool->getTemplates());
    }

    public function testGetTitleLogo(): void
    {
        $this->assertSame('/path/to/pic.png', $this->pool->getTitleLogo());
    }

    public function testGetTitle(): void
    {
        $this->assertSame('Sonata Admin', $this->pool->getTitle());
    }

    public function testGetOption(): void
    {
        $this->assertSame('bar', $this->pool->getOption('foo'));

        $this->assertNull($this->pool->getOption('non_existent_option'));
    }

    public function testOptionDefault(): void
    {
        $this->assertSame([], $this->pool->getOption('nonexistantarray', []));
    }

    private function getItemArray(string $serviceId): array
    {
        return [
            'admin' => $serviceId,
            'label' => '',
            'route' => '',
            'route_params' => [],
        ];
    }
}
