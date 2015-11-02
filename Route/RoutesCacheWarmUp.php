<?php

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

class RoutesCacheWarmUp implements CacheWarmerInterface
{
    protected $cache;

    protected $pool;

    /**
     * @param RoutesCache $cache
     * @param Pool        $pool
     */
    public function __construct(RoutesCache $cache, Pool $pool)
    {
        $this->cache = $cache;
        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        foreach ($this->pool->getAdminServiceIds() as $id) {
            $this->cache->load($this->pool->getInstance($id));
        }
    }
}
