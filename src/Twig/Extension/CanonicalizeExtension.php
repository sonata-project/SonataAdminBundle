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

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class CanonicalizeExtension extends AbstractExtension
{
    // @todo: there are more locales which are not supported by moment and they need to be translated/normalized/canonicalized here
    private const MOMENT_UNSUPPORTED_LOCALES = [
        'de' => ['de', 'de-at'],
        'es' => ['es', 'es-do'],
        'nl' => ['nl', 'nl-be'],
        'fr' => ['fr', 'fr-ca', 'fr-ch'],
    ];

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            //NEXT_MAJOR: Uncomment lines below
            //new TwigFunction('canonicalize_locale_for_moment', [$this, 'getCanonicalizedLocaleForMoment'], ['needs_context' => true]),
            //new TwigFunction('canonicalize_locale_for_select2', [$this, 'getCanonicalizedLocaleForSelect2'], ['needs_context' => true]),
        ];
    }

    /*
     * Returns a canonicalized locale for "moment" NPM library,
     * or `null` if the locale's language is "en", which doesn't require localization.
     */
    public function getCanonicalizedLocaleForMoment(array $context): ?string
    {
        $locale = strtolower(str_replace('_', '-', $context['app']->getRequest()->getLocale()));

        // "en" language doesn't require localization.
        if (('en' === $lang = substr($locale, 0, 2)) && !\in_array($locale, ['en-au', 'en-ca', 'en-gb', 'en-ie', 'en-nz'], true)) {
            return null;
        }

        foreach (self::MOMENT_UNSUPPORTED_LOCALES as $language => $locales) {
            if ($language === $lang && !\in_array($locale, $locales, true)) {
                $locale = $language;
            }
        }

        return $locale;
    }

    /**
     * Returns a canonicalized locale for "select2" NPM library,
     * or `null` if the locale's language is "en", which doesn't require localization.
     */
    public function getCanonicalizedLocaleForSelect2(array $context): ?string
    {
        $locale = str_replace('_', '-', $context['app']->getRequest()->getLocale());

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
}
