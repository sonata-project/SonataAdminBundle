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
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Menu\Matcher\Voter\ActiveVoter;

final class ActiveVoterTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMatching(?bool $itemData, ?bool $expected): void
    {
        $item = $this->createMock(ItemInterface::class);
        $item
            ->method('getExtra')
            ->with(static::logicalOr(
                static::equalTo('active'),
                static::equalTo('sonata_admin')
            ))
            ->willReturnCallback(static function (string $name) use ($itemData) {
                if ('active' === $name) {
                    return $itemData;
                }

                return true;
            });

        $voter = new ActiveVoter();

        static::assertSame($expected, $voter->matchItem($item));
    }

    /**
     * @return iterable<array{bool|null, bool|null}>
     */
    public function provideData(): iterable
    {
        return [
            'active' => [true, true],
            'no active' => [false, false],
            'null' => [null, null],
        ];
    }
}
