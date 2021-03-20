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

namespace Sonata\AdminBundle\Route;

use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class RoutesCacheWarmUp implements CacheWarmerInterface
{
    /**
     * @var RoutesCache
     */
    private $cache;

    /**
     * @var Pool
     */
    private $pool;

    public function __construct(RoutesCache $cache, Pool $pool)
    {
        $this->cache = $cache;
        $this->pool = $pool;
    }

    public function isOptional(): bool
    {
        return true;
    }

    /**
     * NEXT_MAJOR: Add the string param typehint when Symfony 4 support is dropped.
     *
     * @param string $cacheDir
     *
     * @return string[]
     */
    public function warmUp($cacheDir): array
    {
        foreach ($this->pool->getAdminServiceIds() as $id) {
            $this->cache->load($this->pool->getInstance($id));
        }

        return [];
    }
}
