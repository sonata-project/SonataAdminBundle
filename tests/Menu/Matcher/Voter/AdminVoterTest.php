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
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Menu\Matcher\Voter\AdminVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AdminVoterTest extends AbstractVoterTest
{
    /**
     * {@inheritdoc}
     */
    public function provideData()
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
     * @group legacy
     */
    public function testDeprecatedRequestSetter(): void
    {
        $request = new Request();

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $voter = new AdminVoter();
        $voter->setRequest($request);
    }

    /**
     * {@inheritdoc}
     */
    protected function createVoter($dataVoter, $route)
    {
        $request = new Request();
        $request->request->set('_sonata_admin', $dataVoter);
        $request->request->set('_route', $route);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $voter = new AdminVoter($requestStack);

        return $voter;
    }

    /**
     * {@inheritdoc}
     */
    protected function createItem($data)
    {
        $item = $this->getMockForAbstractClass(ItemInterface::class);
        $item->expects($this->any())
             ->method('getExtra')
             ->with($this->logicalOr(
                $this->equalTo('admin'),
                $this->equalTo('route')
             ))
             ->will($this->returnValue($data))
        ;

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    private function getAdmin($code, $list = false, $granted = false)
    {
        $admin = $this->createMock(AbstractAdmin::class);
        $admin
            ->expects($this->any())
            ->method('hasRoute')
            ->with('list')
            ->will($this->returnValue($list))
        ;
        $admin
            ->expects($this->any())
            ->method('hasAccess')
            ->with('list')
            ->will($this->returnValue($granted))
        ;
        $admin
            ->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code))
        ;
        $admin
            ->expects($this->any())
            ->method('getChildren')
            ->will($this->returnValue([]))
        ;

        return $admin;
    }

    /**
     * {@inheritdoc}
     */
    private function getChildAdmin($parentCode, $childCode, $list = false, $granted = false)
    {
        $parentAdmin = $this->createMock(AbstractAdmin::class);
        $parentAdmin
            ->expects($this->any())
            ->method('hasRoute')
            ->with('list')
            ->will($this->returnValue($list))
        ;
        $parentAdmin
            ->expects($this->any())
            ->method('hasAccess')
            ->with('list')
            ->will($this->returnValue($granted))
        ;
        $parentAdmin
            ->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($parentCode))
        ;

        $childAdmin = $this->createMock(AbstractAdmin::class);
        $childAdmin
            ->expects($this->any())
            ->method('getBaseCodeRoute')
            ->will($this->returnValue($parentCode.'|'.$childCode))
        ;

        $parentAdmin
            ->expects($this->any())
            ->method('getChildren')
            ->will($this->returnValue([$childAdmin]))
        ;

        return $parentAdmin;
    }
}
