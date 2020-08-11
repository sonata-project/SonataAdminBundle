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
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\Matcher\Voter\VoterInterface;

// NEXT_MAJOR: Remove the else part when dropping support for knplabs/knp-menu 2.x
if (!method_exists(Matcher::class, 'addVoter')) {
    /**
     * Active menu voter bases in extra `active`.
     *
     * @final since sonata-project/admin-bundle 3.52
     *
     * @author Samusev Andrey <andrey.simfi@ya.ru>
     */
    class ActiveVoter implements VoterInterface
    {
        public function matchItem(ItemInterface $item): ?bool
        {
            if (!$item->getExtra('sonata_admin', false)) {
                return null;
            }

            return $item->getExtra('active', null);
        }
    }
} else {
    /**
     * Active menu voter bases in extra `active`.
     *
     * @final since sonata-project/admin-bundle 3.52
     *
     * @author Samusev Andrey <andrey.simfi@ya.ru>
     */
    class ActiveVoter implements VoterInterface
    {
        public function matchItem(ItemInterface $item)
        {
            if (!$item->getExtra('sonata_admin', false)) {
                return null;
            }

            return $item->getExtra('active', null);
        }
    }
}
