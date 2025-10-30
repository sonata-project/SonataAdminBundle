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

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

final class CanonicalizeRuntime implements RuntimeExtensionInterface
{
    /**
     * @internal This class should only be used through Twig
     */
    public function __construct(private RequestStack $requestStack)
    {
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle 4.40.0
     *
     * @phpstan-ignore return.unusedType
     */
    public function getCanonicalizedLocaleForMoment(): ?string
    {
        trigger_deprecation(
            'sonata-project/admin-bundle',
            '4.40.0',
            'Method "%s" is deprecated and no-op. It always returns null and will be removed in 5.0.',
            __METHOD__,
        );

        return null;
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
