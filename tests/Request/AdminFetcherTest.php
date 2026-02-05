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

namespace SensioLabs\AdminBundle\Tests\Request;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\Exception\AdminCodeNotFoundException;
use SensioLabs\AdminBundle\Request\AdminFetcher;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

final class AdminFetcherTest extends TestCase
{
    private AdminFetcher $adminFetcher;

    /**
     * @var MockObject&AdminInterface<object>
     */
    private AdminInterface $admin;

    protected function setUp(): void
    {
        $this->admin = $this->createMock(AdminInterface::class);

        $container = new Container();
        $container->set('sensiolabs.admin.post', $this->admin);

        $this->adminFetcher = new AdminFetcher(new Pool($container, ['sensiolabs.admin.post']));
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
        $request->attributes->set('_sensiolabs_admin', 'non_existing_admin_code');

        $this->expectException(AdminCodeNotFoundException::class);

        $this->adminFetcher->get($request);
    }

    public function testSetsUniqIdToAdmin(): void
    {
        $request = new Request();
        $request->attributes->set('_sensiolabs_admin', 'sensiolabs.admin.post');
        $uniqueId = 'uniqid_post_id';
        $request->query->set('uniqid', $uniqueId);

        $this->admin
            ->expects(static::once())
            ->method('setUniqId')
            ->with($uniqueId);

        $this->adminFetcher->get($request);
    }

    public function testSetsRequestToRootAdmin(): void
    {
        $request = new Request();
        $request->attributes->set('_sensiolabs_admin', 'sensiolabs.admin.post');

        $this->admin
            ->expects(static::once())
            ->method('isChild')
            ->willReturn(true);

        $adminParent = $this->createMock(AdminInterface::class);

        $this->admin
            ->expects(static::once())
            ->method('getParent')
            ->willReturn($adminParent);

        $this->admin
            ->expects(static::once())
            ->method('setCurrentChild')
            ->with(true);

        $adminParent
            ->expects(static::once())
            ->method('setRequest')
            ->with($request);

        $this->adminFetcher->get($request);
    }
}
