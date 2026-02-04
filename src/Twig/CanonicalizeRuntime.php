<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Twig;

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
     * No-op method that always returns null.
     * Moment.js localization is handled differently now.
     */
    public function getCanonicalizedLocaleForMoment(): ?string
    {
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
