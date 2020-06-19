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

/**
 * NEXT_MAJOR: Remove this class.
 *
 * @internal
 *
 * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0.
 *
 * This class is used to support `Sonata\AdminBundle\Twig\Extension\StringExtensions` when `sonata_admin.options.legacy_twig_text_extension`
 * is set to true and deprecated `twig/extensions` is not installed. It is copy of required function, which keep BC for `sonata_truncate`
 * twig filter until sonata-project/admin-bundle 4.0 where this filter will be dropped.
 */
final class DeprecatedTextExtension extends AbstractExtension
{
    public function twigTruncateFilter(Environment $env, ?string $value, int $length = 30, bool $preserve = false, $separator = '...')
    {
        if (\function_exists('mb_get_info')) {
            if (mb_strlen($value, $env->getCharset()) > $length) {
                if ($preserve) {
                    // If breakpoint is on the last word, return the value without separator.
                    if (false === ($breakpoint = mb_strpos($value, ' ', $length, $env->getCharset()))) {
                        return $value;
                    }

                    $length = $breakpoint;
                }

                return rtrim(mb_substr($value, 0, $length, $env->getCharset())).$separator;
            }
        } else {
            if (\strlen($value) > $length) {
                if ($preserve) {
                    if (false !== ($breakpoint = strpos($value, ' ', $length))) {
                        $length = $breakpoint;
                    }
                }

                return rtrim(substr($value, 0, $length)).$separator;
            }
        }

        return $value;
    }
}
