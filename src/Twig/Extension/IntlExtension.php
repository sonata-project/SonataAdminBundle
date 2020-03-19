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

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extra\Intl\IntlExtension as TwigIntlExtension;
use Twig\TwigFilter;

/**
 * @internal
 */
final class IntlExtension extends AbstractExtension
{
    /**
     * @var TwigIntlExtension
     */
    private $extension;

    public function __construct(TwigIntlExtension $extension)
    {
        $this->extension = $extension;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('sonata_format_currency', [$this, 'formatCurrency']),
            new TwigFilter('sonata_format_number', [$this, 'formatNumber']),
            new TwigFilter('sonata_format_datetime', [$this, 'formatDateTime'], ['needs_environment' => true]),
            new TwigFilter('sonata_format_date', [$this, 'formatDate'], ['needs_environment' => true]),
        ];
    }

    public function formatCurrency($amount, string $currency, array $attrs = [], string $locale = null): string
    {
        return $this->extension->formatCurrency($amount, $currency, $attrs, $locale);
    }

    public function formatNumber($number, array $attrs = [], string $style = 'decimal', string $type = 'default', string $locale = null): string
    {
        return $this->extension->formatNumber($number, $attrs, $style, $type, $locale);
    }

    public function formatDateTime(Environment $env, $date, ?string $dateFormat = 'medium', ?string $timeFormat = 'medium', string $pattern = '', $timezone = null, string $calendar = 'gregorian', string $locale = null): string
    {
        return $this->extension->formatDateTime($env, $date, $dateFormat, $timeFormat, $pattern, $timezone, $calendar, $locale);
    }

    public function formatDate(Environment $env, $date, ?string $dateFormat = 'medium', string $pattern = '', $timezone = null, string $calendar = 'gregorian', string $locale = null): string
    {
        return $this->extension->formatDateTime($env, $date, $dateFormat, 'none', $pattern, $timezone, $calendar, $locale);
    }
}
