<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Menu\Matcher\Voter;

use Sonata\AdminBundle\Menu\Matcher\Voter\AdminVoter;
use Symfony\Component\HttpFoundation\Request;

class AdminVoterTest extends AbstractVoterTest
{
    /**
     * {@inheritdoc}
     */
    public function provideData()
    {
        return array(
            'no data' => array(null, null, null, null),
            'no route and granted' => array($this->getAdmin('_sonata_admin'), '_sonata_admin', null, null),
            'no granted' => array($this->getAdmin('_sonata_admin', true, false), '_sonata_admin', null, null),
            'no code' => array($this->getAdmin('_sonata_admin_code', true, true), '_sonata_admin', null, null),
            'no code request' => array($this->getAdmin('_sonata_admin', true, true), '_sonata_admin_unexpected', null, null),
            'no route' => array($this->getAdmin('_sonata_admin', false, true), '_sonata_admin', null, null),
            'has admin' => array($this->getAdmin('_sonata_admin', true, true), '_sonata_admin', null, true),
            'direct link' => array('admin_post', null, 'admin_post', true),
            'no direct link' => array('admin_post', null, 'admin_blog', null),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function createVoter($dataVoter, $route)
    {
        $voter = new AdminVoter();
        $request = new Request();
        $request->request->set('_sonata_admin', $dataVoter);
        $request->request->set('_route', $route);
        $voter->setRequest($request);

        return $voter;
    }

    /**
     * {@inheritdoc}
     */
    protected function createItem($data)
    {
        $item = $this->getMock('Knp\Menu\ItemInterface');
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
        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin
            ->expects($this->any())
            ->method('hasRoute')
            ->with('list')
            ->will($this->returnValue($list))
        ;
        $admin
            ->expects($this->any())
            ->method('isGranted')
            ->with('LIST')
            ->will($this->returnValue($granted))
        ;
        $admin
            ->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code))
        ;

        return $admin;
    }
}
