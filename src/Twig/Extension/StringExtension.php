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

use Symfony\Component\String\UnicodeString as SymfonyUnicodeString;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * NEXT_MAJOR: Remove this class.
 *
 * Decorates `Twig\Extra\String\StringExtension` in order to provide the `$cut`
 * argument for `Symfony\Component\String\UnicodeString::truncate()`.
 * This class must be removed when the component ships this feature.
 *
 * @internal
 *
 * @see https://github.com/symfony/symfony/pull/35649
 * @deprecated since sonata-project/admin-bundle 3.69, to be removed in 4.0.
 *
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class StringExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('sonata_truncate', [$this, 'legacyTruncteWithUnicodeString']),
        ];
    }

    public function legacyTruncteWithUnicodeString(?string $text, int $length = 30, bool $preserve = false, string $ellipsis = '...'): SymfonyUnicodeString
    {
        @trigger_error(
            'The "sonata_truncate" twig filter is deprecated'
            .' since sonata-project/admin-bundle 3.69 and will be removed in 4.0. Use "u.truncate" instead.',
            E_USER_DEPRECATED
        );

        return (new SymfonyUnicodeString($text ?? ''))->truncate($length, $ellipsis, $preserve);
    }
}
