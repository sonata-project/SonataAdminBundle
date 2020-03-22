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

use Symfony\Component\String\UnicodeString as DecoratedUnicodeString;
use Twig\Extension\AbstractExtension;
use Twig\Extra\String\StringExtension as TwigStringExtension;
use Twig\TwigFilter;

/**
 * Decorates `Twig\Extra\String\StringExtension` in order to provide the `$cut`
 * argument for `Symfony\Component\String\UnicodeString::truncate()`.
 * This class must be removed when the component ships this feature.
 *
 * @internal
 *
 * @see https://github.com/symfony/symfony/pull/35649
 *
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class StringExtension extends AbstractExtension
{
    /**
     * @var TwigStringExtension
     */
    private $extension;

    public function __construct(TwigStringExtension $extension)
    {
        $this->extension = $extension;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('truncate', [$this, 'legacyTruncteWithUnicodeString']),
        ];
    }

    /**
     * NEXT_MAJOR: Fix the arguments in order to respect the signature at `UnicodeString::truncate()`.
     */
    public function legacyTruncteWithUnicodeString(?string $text, int $length = 30, bool $preserve = false, string $ellipsis = '...'): DecoratedUnicodeString
    {
        return (new UnicodeString($text ?? ''))->truncate($length, $ellipsis, $preserve);
    }
}
