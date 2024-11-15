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

use Sonata\Form\Twig\CanonicalizeRuntime as SonataFormCanonicalizeRuntime;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

/** @psalm-suppress UndefinedClass */
final class CanonicalizeRuntime implements RuntimeExtensionInterface
{
    /**
     * TODO: Remove second argument when dropping support for `sonata-project/form-extensions` 1.x.
     *
     * @internal This class should only be used through Twig
     */
    public function __construct(
        private RequestStack $requestStack,
        private ?SonataFormCanonicalizeRuntime $canonicalizeRuntime = null, // @phpstan-ignore-line
    ) {
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * Returns a canonicalized locale for "Moment.js" NPM library,
     * or `null` if the locale's language is "en", which doesn't require localization.
     */
    public function getCanonicalizedLocaleForMoment(): ?string
    {
        if (null === $this->canonicalizeRuntime) {
            return null;
        }

        // @phpstan-ignore-next-line
        return $this->canonicalizeRuntime->getCanonicalizedLocaleForMoment();
    }

    /**
     * Returns a canonicalized locale for "select2" NPM library,
     * or `null` if the locale's language is "en", which doesn't require localization.
     */
    public function getCanonicalizedLocaleForSelect2(): ?string
    {
        $locale = $this->getLocale();

        // "en" language doesn't require localization.
        if ('en' === $lang = substr($locale, 0, 2)) {
            return null;
        }

        switch ($locale) {
            case 'pt':
                $locale = 'pt-PT';
                break;
            case 'ug':
                $locale = 'ug-CN';
                break;
            case 'zh':
                $locale = 'zh-CN';
                break;
            default:
                if (!\in_array($locale, ['pt-BR', 'pt-PT', 'ug-CN', 'zh-CN', 'zh-TW'], true)) {
                    $locale = $lang;
                }
        }

        return $locale;
    }

    private function getLocale(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            throw new \LogicException('The request stack is empty.');
        }

        return str_replace('_', '-', $request->getLocale());
    }
}
