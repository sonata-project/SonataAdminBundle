<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Menu\Matcher\Voter;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Menu\Matcher\Voter\ActiveVoter;

final class ActiveVoterTest extends TestCase
{
    #[DataProvider('provideMatchingCases')]
    public function testMatching(?bool $itemData, ?bool $expected): void
    {
        $item = $this->createMock(ItemInterface::class);
        $item
            ->method('getExtra')
            ->with(static::logicalOr(
                static::equalTo('active'),
                static::equalTo('sensiolabs_admin')
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
    public static function provideMatchingCases(): iterable
    {
        yield 'active' => [true, true];
        yield 'no active' => [false, false];
        yield 'null' => [null, null];
    }
}
