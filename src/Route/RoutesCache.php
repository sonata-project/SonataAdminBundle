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

use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class RoutesCache
{
    /**
     * @var string
     */
    private $cacheFolder;

    /**
     * @var bool
     */
    private $debug;

    public function __construct(string $cacheFolder, bool $debug)
    {
        $this->cacheFolder = $cacheFolder;
        $this->debug = $debug;
    }

    /**
     * @param AdminInterface<object> $admin
     *
     * @throws \RuntimeException
     *
     * @return array<string, string>
     *
     * @phpstan-template T of object
     * @phpstan-param AdminInterface<T> $admin
     */
    public function load(AdminInterface $admin): array
    {
        $filename = sprintf('%s/route_%s', $this->cacheFolder, md5($admin->getCode()));

        $cache = new ConfigCache($filename, $this->debug);
        if ($cache->isFresh()) {
            $content = file_get_contents($filename);
            if (false !== $content) {
                $routes = unserialize($content);
                if (false !== $routes) {
                    return $routes;
                }
            }
        }

        $resources = [];
        $routes = [];

        $reflection = new \ReflectionObject($admin);
        if (false !== $reflection->getFileName() && file_exists($reflection->getFileName())) {
            $resources[] = new FileResource($reflection->getFileName());
        }

        foreach ($admin->getRoutes()->getElements() as $code => $route) {
            $routes[$code] = $route->getDefault('_sonata_name');
        }

        foreach ($admin->getExtensions() as $extension) {
            $reflection = new \ReflectionObject($extension);
            if (false !== $reflection->getFileName() && file_exists($reflection->getFileName())) {
                $resources[] = new FileResource($reflection->getFileName());
            }
        }

        $cache->write(serialize($routes), $resources);

        return $routes;
    }
}
