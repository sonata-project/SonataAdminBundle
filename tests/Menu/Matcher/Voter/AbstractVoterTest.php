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
use PHPUnit\Framework\TestCase;

abstract class AbstractVoterTest extends TestCase
{
    /**
     * @phpstan-return array<array{mixed, mixed, mixed, bool|null}>
     */
    abstract public function provideData(): array;

    /**
     * @param mixed $itemData
     * @param mixed $voterData
     * @param mixed $route
     *
     * @dataProvider provideData
     */
    public function testMatching($itemData, $voterData, $route, ?bool $expected): void
    {
        $item = $this->createItem($itemData);
        $voter = $this->createVoter($voterData, $route);

        $this->assertSame($expected, $voter->matchItem($item));
    }

    /**
     * @param mixed $dataVoter
     * @param mixed $route
     */
    abstract protected function createVoter($dataVoter, $route): VoterInterface;

    /**
     * @param mixed $data
     */
    abstract protected function createItem($data): ItemInterface;
}
