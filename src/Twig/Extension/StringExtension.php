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
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extensions\TextExtension;
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
     * @var AbstractExtension|null
     */
    private $legacyExtension;

    public function __construct(?AbstractExtension $legacyExtension = null)
    {
        if (null !== $legacyExtension
            && !$legacyExtension instanceof TextExtension
            && !$legacyExtension instanceof DeprecatedTextExtension
        ) {
            throw new \TypeError(sprintf(
                'Argument 1 passed to %s::__construct() must be instance of %s or %s or null',
                self::class,
                TextExtension::class,
                DeprecatedTextExtension::class
            ));
        }

        $this->legacyExtension = $legacyExtension;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('sonata_truncate', [$this, 'deprecatedTruncate'], ['needs_environment' => true]),
        ];
    }

    /**
     * @return SymfonyUnicodeString|string
     */
    public function deprecatedTruncate(Environment $env, ?string $text, int $length = 30, bool $preserve = false, string $ellipsis = '...')
    {
        @trigger_error(
            'The "sonata_truncate" twig filter is deprecated'
            .' since sonata-project/admin-bundle 3.69 and will be removed in 4.0. Use "u.truncate" instead.',
            E_USER_DEPRECATED
        );

        if ($this->legacyExtension instanceof TextExtension) {
            return twig_truncate_filter($env, $text, $length, $preserve, $ellipsis);
        }

        if ($this->legacyExtension instanceof DeprecatedTextExtension) {
            return $this->legacyExtension->twigTruncateFilter($env, $text, $length, $preserve, $ellipsis);
        }

        return $this->legacyTruncteWithUnicodeString($text, $length, $preserve, $ellipsis);
    }

    public function legacyTruncteWithUnicodeString(?string $text, int $length = 30, bool $preserve = false, string $ellipsis = '...'): SymfonyUnicodeString
    {
        return (new SymfonyUnicodeString($text ?? ''))->truncate($length, $ellipsis, $preserve);
    }
}
