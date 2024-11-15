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

use Sonata\AdminBundle\Twig\CanonicalizeRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class CanonicalizeExtension extends AbstractExtension
{
    /**
     * NEXT_MAJOR: Remove this constructor.
     *
     * @internal This class should only be used through Twig
     */
    public function __construct(
        private CanonicalizeRuntime $canonicalizeRuntime,
    ) {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('canonicalize_locale_for_moment', [CanonicalizeRuntime::class, 'getCanonicalizedLocaleForMoment']),
            new TwigFunction('canonicalize_locale_for_select2', [CanonicalizeRuntime::class, 'getCanonicalizedLocaleForSelect2']),
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle version 4.7 use CanonicalizeRuntime::getCanonicalizedLocaleForMoment() instead
     *
     * Returns a canonicalized locale for "moment" NPM library,
     * or `null` if the locale's language is "en", which doesn't require localization.
     */
    public function getCanonicalizedLocaleForMoment(): ?string
    {
        @trigger_error(\sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            CanonicalizeRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->canonicalizeRuntime->getCanonicalizedLocaleForMoment();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle version 4.7 use CanonicalizeRuntime::getCanonicalizedLocaleForSelect2() instead
     *
     * Returns a canonicalized locale for "select2" NPM library,
     * or `null` if the locale's language is "en", which doesn't require localization.
     */
    public function getCanonicalizedLocaleForSelect2(): ?string
    {
        @trigger_error(\sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            CanonicalizeRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->canonicalizeRuntime->getCanonicalizedLocaleForSelect2();
    }
}
