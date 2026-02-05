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
     * @deprecated This method is deprecated and will be removed in a future version.
     *             Select2 has been replaced with Tom Select which handles localization differently.
     */
    public function getCanonicalizedLocaleForSelect2(): ?string
    {
        trigger_deprecation(
            'sensiolabs-de/admin-bundle',
            '5.0',
            'The "%s()" method is deprecated. Select2 has been replaced with Tom Select.',
            __METHOD__
        );

        return null;
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
