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

namespace Sonata\AdminBundle\Tests\Request;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Exception\AdminCodeNotFoundException;
use Sonata\AdminBundle\Request\AdminFetcher;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

final class AdminFetcherTest extends TestCase
{
    /**
     * @var AdminFetcher
     */
    private $adminFetcher;

    /**
     * @var MockObject&AdminInterface
     */
    private $admin;

    protected function setUp(): void
    {
        $this->admin = $this->createMock(AdminInterface::class);

        $container = new Container();
        $container->set('sonata.admin.post', $this->admin);

        $this->adminFetcher = new AdminFetcher(new Pool($container, ['sonata.admin.post']));
    }

    public function testGetItThrowsAnExceptionWithoutAdminCode(): void
    {
        $request = new Request();

        $this->expectException(\InvalidArgumentException::class);

        $this->adminFetcher->get($request);
    }

    public function testGetItThrowsAnExceptionIfThereIsNoAdminWithAdminCodeGiven(): void
    {
        $request = new Request();
        $request->attributes->set('_sonata_admin', 'non_existing_admin_code');

        $this->expectException(AdminCodeNotFoundException::class);

        $this->adminFetcher->get($request);
    }

    public function testSetsUniqidToAdmin(): void
    {
        $request = new Request();
        $request->attributes->set('_sonata_admin', 'sonata.admin.post');
        $uniqueId = 'uniqid_post_id';
        $request->query->set('uniqid', $uniqueId);

        $this->admin
            ->expects($this->once())
            ->method('setUniqid')
            ->with($uniqueId);

        $this->adminFetcher->get($request);
    }

    public function testSetsRequestToRootAdmin(): void
    {
        $request = new Request();
        $request->attributes->set('_sonata_admin', 'sonata.admin.post');

        $this->admin
            ->expects($this->once())
            ->method('isChild')
            ->willReturn(true);

        $adminParent = $this->createMock(AdminInterface::class);

        $this->admin
            ->expects($this->once())
            ->method('getParent')
            ->willReturn($adminParent);

        $this->admin
            ->expects($this->once())
            ->method('setCurrentChild')
            ->with(true);

        $adminParent
            ->expects($this->once())
            ->method('setRequest')
            ->with($request);

        $this->adminFetcher->get($request);
    }
}
