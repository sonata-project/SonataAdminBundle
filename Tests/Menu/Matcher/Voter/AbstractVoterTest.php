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
use Knp\Menu\Matcher\Voter\VoterInterface;

abstract class AbstractVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    abstract public function provideData();

    /**
     * @param mixed $dataVoter
     *
     * @return VoterInterface
     */
    abstract protected function createVoter($dataVoter);

    /**
     * @param mixed $data
     *
     * @return ItemInterface
     */
    abstract protected function createItem($data);

    /**
     * @param mixed     $itemData
     * @param mixed     $voterData
     * @param bool|null $expected
     *
     * @dataProvider provideData
     */
    public function testMatching($itemData, $voterData, $expected)
    {
        $item = $this->createItem($itemData);
        $voter = $this->createVoter($voterData);
        $this->assertSame($expected, $voter->matchItem($item));
    }
}
