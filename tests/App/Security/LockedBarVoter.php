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

namespace Sonata\AdminBundle\Tests\App\Security;

use Sonata\AdminBundle\Tests\App\Model\Bar;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * An object based voter: nobody may see the history of the bar "locked", even with the history
 * role. Together with the "unanimous" strategy of the "role_security" environment, this denial wins
 * over the role voter, but only when the object is passed to the access check.
 *
 * @extends Voter<string, Bar>
 */
final class LockedBarVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return 'ROLE_SONATA_BAR_ADMIN_HISTORY' === $attribute && $subject instanceof Bar;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, mixed ...$arguments): bool
    {
        return 'locked' !== $subject->getId();
    }
}
