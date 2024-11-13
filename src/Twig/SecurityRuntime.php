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

namespace Sonata\AdminBundle\Twig;

use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Twig\Extension\RuntimeExtensionInterface;

final class SecurityRuntime implements RuntimeExtensionInterface
{
    /**
     * @internal This class should only be used through Twig
     */
    public function __construct(
        private ?AuthorizationCheckerInterface $securityChecker = null,
    ) {
    }

    /**
     * @param string|string[] $role
     */
    public function isGrantedAffirmative(string|array $role, ?object $object = null, ?string $field = null): bool
    {
        if (null === $this->securityChecker) {
            return false;
        }

        if (null !== $field) {
            $object = new FieldVote($object, $field);
        }

        if (!\is_array($role)) {
            $role = [$role];
        }

        foreach ($role as $oneRole) {
            try {
                if ($this->securityChecker->isGranted($oneRole, $object)) {
                    return true;
                }
            } catch (AuthenticationCredentialsNotFoundException) {
                // empty on purpose
            }
        }

        return false;
    }
}
