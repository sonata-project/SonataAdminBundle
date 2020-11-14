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

namespace Sonata\AdminBundle\Tests\Menu\Matcher\Voter;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Menu\Matcher\Voter\AdminVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AdminVoterTest extends AbstractVoterTest
{
    /**
     * {@inheritdoc}
     */
    public function provideData(): array
    {
        return [
            'no data' => [null, null, null, null],
            'no route and granted' => [$this->getAdmin('_sonata_admin'), '_sonata_admin', null, null],
            'no granted' => [$this->getAdmin('_sonata_admin', true, false), '_sonata_admin', null, null],
            'no code' => [$this->getAdmin('_sonata_admin_code', true, true), '_sonata_admin', null, null],
            'no code request' => [$this->getAdmin('_sonata_admin', true, true), '_sonata_admin_unexpected', null, null],
            'no route' => [$this->getAdmin('_sonata_admin', false, true), '_sonata_admin', null, null],
            'has admin' => [$this->getAdmin('_sonata_admin', true, true), '_sonata_admin', null, true],
            'has child admin' => [$this->getChildAdmin('_sonata_admin', '_sonata_child_admin', true, true), '_sonata_admin|_sonata_child_admin', null, true],
            'has bad child admin' => [$this->getChildAdmin('_sonata_admin', '_sonata_child_admin', true, true), '_sonata_admin|_sonata_child_admin_unexpected', null, null],
            'direct link' => ['admin_post', null, 'admin_post', true],
            'no direct link' => ['admin_post', null, 'admin_blog', null],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function createVoter($dataVoter, $route): VoterInterface
    {
        $request = new Request();
        $request->request->set('_sonata_admin', $dataVoter);
        $request->request->set('_route', $route);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        return new AdminVoter($requestStack);
    }

    /**
     * {@inheritdoc}
     */
    protected function createItem($data): ItemInterface
    {
        $item = $this->getMockForAbstractClass(ItemInterface::class);
        $item
            ->method('getExtra')
            ->with($this->logicalOr(
                $this->equalTo('admin'),
                $this->equalTo('route')
            ))
            ->willReturn($data)
        ;

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    private function getAdmin(string $code, bool $list = false, bool $granted = false): AbstractAdmin
    {
        $admin = $this->createMock(AbstractAdmin::class);
        $admin
            ->method('hasRoute')
            ->with('list')
            ->willReturn($list)
        ;
        $admin
            ->method('hasAccess')
            ->with('list')
            ->willReturn($granted)
        ;
        $admin
            ->method('getCode')
            ->willReturn($code)
        ;
        $admin
            ->method('getChildren')
            ->willReturn([])
        ;

        return $admin;
    }

    /**
     * {@inheritdoc}
     */
    private function getChildAdmin(
        string $parentCode,
        string $childCode,
        bool $list = false,
        bool $granted = false
    ): AbstractAdmin {
        $parentAdmin = $this->createMock(AbstractAdmin::class);
        $parentAdmin
            ->method('hasRoute')
            ->with('list')
            ->willReturn($list)
        ;
        $parentAdmin
            ->method('hasAccess')
            ->with('list')
            ->willReturn($granted)
        ;
        $parentAdmin
            ->method('getCode')
            ->willReturn($parentCode)
        ;

        $childAdmin = $this->createMock(AbstractAdmin::class);
        $childAdmin
            ->method('getBaseCodeRoute')
            ->willReturn(sprintf('%s|%s', $parentCode, $childCode))
        ;

        $parentAdmin
            ->method('getChildren')
            ->willReturn([$childAdmin])
        ;

        return $parentAdmin;
    }
}
