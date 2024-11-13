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

namespace Sonata\AdminBundle\Twig\Extension;

use Sonata\AdminBundle\Twig\SecurityRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SecurityExtension extends AbstractExtension
{
    /**
     * NEXT_MAJOR: Remove this constructor.
     *
     * @internal This class should only be used through Twig
     */
    public function __construct(
        private SecurityRuntime $securityRuntime,
    ) {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_granted_affirmative', [SecurityRuntime::class, 'isGrantedAffirmative']),
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle version 4.7 use SecurityRuntime::isGrantedAffirmative() instead
     *
     * @param string|string[] $role
     */
    public function isGrantedAffirmative(string|array $role, ?object $object = null, ?string $field = null): bool
    {
        @trigger_error(\sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            SecurityRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->securityRuntime->isGrantedAffirmative($role, $object, $field);
    }
}
