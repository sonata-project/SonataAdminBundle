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

namespace Sonata\AdminBundle\BCLayer;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * This class is a BC layer for user interface for symfony/security-core < 5.3.
 * Remove this class when dropping support for symfony/security-core < 5.3.
 *
 * @internal
 */
final class BCUserInterface
{
    /**
     * @psalm-suppress DeprecatedMethod
     */
    public static function getUsername(UserInterface $user): string
    {
        // @phpstan-ignore-next-line
        if (method_exists($user, 'getUserIdentifier')) {
            return $user->getUserIdentifier();
        }

        return $user->getUsername();
    }
}
