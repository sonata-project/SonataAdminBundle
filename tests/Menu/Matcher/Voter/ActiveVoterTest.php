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
use Sonata\AdminBundle\Menu\Matcher\Voter\ActiveVoter;

class ActiveVoterTest extends AbstractVoterTest
{
    public function createVoter($dataVoter, $route): VoterInterface
    {
        return new ActiveVoter();
    }

    public function provideData(): array
    {
        return [
            'active' => [true, null, true, true],
            'no active' => [false, null, false, false],
            'null' => [null, null, null, null],
        ];
    }

    /**
     * @param mixed $data
     */
    protected function createItem($data): ItemInterface
    {
        $item = $this->getMockForAbstractClass(ItemInterface::class);
        $item
            ->method('getExtra')
            ->with(self::logicalOr(
                self::equalTo('active'),
                self::equalTo('sonata_admin')
            ))
            ->willReturnCallback(static function (string $name) use ($data) {
                if ('active' === $name) {
                    return $data;
                }

                return true;
            });

        return $item;
    }
}
