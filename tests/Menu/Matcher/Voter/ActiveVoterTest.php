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
        return [
            'active' => [true, null, true, true],
            'no active' => [false, null, false, false],
            'null' => [null, null, null, null],
        ];
    }

    /**
     * @param mixed $data
     *
     * @return ItemInterface
     */
    protected function createItem($data)
    {
        $item = $this->getMockForAbstractClass(ItemInterface::class);
        $item->expects($this->any())
             ->method('getExtra')
             ->with($this->logicalOr(
                $this->equalTo('active'),
                $this->equalTo('sonata_admin')
             ))
             ->will($this->returnCallback(function ($name) use ($data) {
                 if ('active' === $name) {
                     return $data;
                 }

                 return true;
             }))
        ;

        return $item;
    }
}
