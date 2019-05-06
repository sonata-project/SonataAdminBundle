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
use Knp\Menu\Matcher\Matcher;
use Sonata\AdminBundle\Menu\Matcher\Voter\ChildrenVoter;

/**
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 * @group legacy
 */
class ChildrenVoterTest extends AbstractVoterTest
{
    /**
     * {@inheritdoc}
     */
    public function provideData()
    {
        return [
            'with no current' => [[false, false], null, new Matcher(), null],
            'with current' => [[true, false], null, new Matcher(), true],
            'with single child' => [[true], null, new Matcher(), true],
        ];
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
        $childItems = [];
        foreach ($data as $childData) {
            $childItem = $this->getMockForAbstractClass(ItemInterface::class);
            $childItem->expects($this->any())
                ->method('isCurrent')
                ->willReturn($childData);
            $childItems[] = $childItem;
        }

        $item = $this->getMockForAbstractClass(ItemInterface::class);
        $item->expects($this->any())
            ->method('getChildren')
            ->willReturn($childItems);

        $item->expects($this->any())
             ->method('getExtra')
             ->with('sonata_admin')
             ->willReturn(true);

        return $item;
    }
}
