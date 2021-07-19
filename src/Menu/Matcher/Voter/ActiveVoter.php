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

namespace Sonata\AdminBundle\Menu\Matcher\Voter;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;

/**
 * Active menu voter bases in extra `active`.
 *
 * @author Samusev Andrey <andrey.simfi@ya.ru>
 */
final class ActiveVoter implements VoterInterface
{
    public function matchItem(ItemInterface $item): ?bool
    {
        if (false === $item->getExtra('sonata_admin', false)) {
            return null;
        }

        return $item->getExtra('active', null);
    }
}
