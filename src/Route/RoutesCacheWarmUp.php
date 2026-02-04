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

namespace SensioLabs\AdminBundle\Route;

use SensioLabs\AdminBundle\Admin\Pool;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class RoutesCacheWarmUp implements CacheWarmerInterface
{
    public function __construct(
        private RoutesCache $cache,
        private Pool $pool,
    ) {
    }

    public function isOptional(): bool
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        foreach ($this->pool->getAdminServiceCodes() as $code) {
            $this->cache->load($this->pool->getInstance($code));
        }

        return [];
    }
}
