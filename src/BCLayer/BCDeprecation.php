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

namespace Sonata\AdminBundle\BCLayer;

use Symfony\Component\Config\Definition\BaseNode;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Remove this class when dropping support for symfony < 5.1.
 *
 * @internal
 */
final class BCDeprecation
{
    /**
     * This class is a BC layer for deprecation messages for symfony/config < 5.1.
     * Remove this method when dropping support for symfony/config < 5.1.
     *
     * @return string[]
     */
    public static function forConfig(string $message, string $version): array
    {
        // @phpstan-ignore-next-line
        if (method_exists(BaseNode::class, 'getDeprecation')) {
            return [
                'sonata-project/admin-bundle',
                $version,
                $message,
            ];
        }

        return [$message];
    }

    /**
     * This class is a BC layer for deprecation messages for symfony/options-resolver < 5.1.
     * Remove this method when dropping support for symfony/options-resolver < 5.1.
     *
     * @param string|\Closure $message
     *
     * @return mixed[]
     */
    public static function forOptionResolver($message, string $version): array
    {
        // @phpstan-ignore-next-line
        if (method_exists(OptionsResolver::class, 'define')) {
            return [
                'sonata-project/admin-bundle',
                $version,
                $message,
            ];
        }

        return [$message];
    }
}
