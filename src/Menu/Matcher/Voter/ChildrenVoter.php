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
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;

@trigger_error(sprintf(
    '"%s" is deprecated since 3.28, will be removed in 4.0.',
    ChildrenVoter::class
));

/**
 * Children menu voter based on children items.
 *
 * @author Samusev Andrey <andrey.simfi@ya.ru>
 *
 * @deprecated since sonata-project/admin-bundle 3.28, will be removed in 4.0.
 */
class ChildrenVoter implements VoterInterface
{
    /**
     * @var MatcherInterface
     */
    private $matcher;

    /**
     * ChildrenVoter constructor.
     */
    public function __construct(MatcherInterface $matcher)
    {
        $this->matcher = $matcher;
    }

    public function matchItem(ItemInterface $item)
    {
        if (!$item->getExtra('sonata_admin', false)) {
            return null;
        }

        $children = $item->getChildren();
        $match = null;
        foreach ($children as $child) {
            if ($this->matcher->isCurrent($child)) {
                $match = true;

                break;
            }
        }

        return $match;
    }
}
