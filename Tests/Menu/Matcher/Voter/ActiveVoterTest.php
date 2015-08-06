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

use Knp\Menu\ItemInterface;
use Sonata\AdminBundle\Menu\Matcher\Voter\ActiveVoter;

class ActiveVoterTest extends AbstractVoterTest
{
    /**
     * {@inheritdoc}
     */
    public function createVoter($dataVoter, $route)
    {
        return new ActiveVoter();
    }

    /**
     * {@inheritdoc}
     */
    public function provideData()
    {
        return array(
            'active'    => array(true, null, true, true),
            'no active' => array(false, null, false, false),
            'null'      => array(null, null, null, null),
        );
    }

    /**
     * @param mixed $data
     *
     * @return ItemInterface
     */
    protected function createItem($data)
    {
        $item = $this->getMock('Knp\Menu\ItemInterface');
        $item->expects($this->any())
             ->method('getExtra')
             ->with('active')
             ->will($this->returnValue($data))
        ;

        return $item;
    }
}
