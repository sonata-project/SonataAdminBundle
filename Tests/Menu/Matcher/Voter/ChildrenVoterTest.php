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

use Knp\Menu\Matcher\Matcher;
use Sonata\AdminBundle\Menu\Matcher\Voter\ChildrenVoter;

/**
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
class ChildrenVoterTest extends AbstractVoterTest
{
    /**
     * {@inheritdoc}
     */
    public function provideData()
    {
        return array(
            'with no current' => array(array(false, false), null, new Matcher(), null),
            'with current' => array(array(true, false), null, new Matcher(), true),
            'with single child' => array(array(true), null, new Matcher(), true),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function createVoter($dataVoter, $route)
    {
        return new ChildrenVoter($route);
    }

    /**
     * {@inheritdoc}
     */
    protected function createItem($data)
    {
        $childItems = array();
        foreach ($data as $childData) {
            $childItem = $this->getMock('Knp\Menu\ItemInterface');
            $childItem->expects($this->any())
                ->method('isCurrent')
                ->willReturn($childData);
            $childItems[] = $childItem;
        }

        $item = $this->getMock('Knp\Menu\ItemInterface');
        $item->expects($this->any())
            ->method('getChildren')
            ->will($this->returnValue($childItems));

        return $item;
    }
}
