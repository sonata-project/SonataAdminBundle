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
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class AdminPoolLoader extends Loader
{
    public const ROUTE_TYPE_NAME = 'sonata_admin';

    public function __construct(
        private Pool $pool,
    ) {
        parent::__construct();
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return self::ROUTE_TYPE_NAME === $type;
    }

    public function load(mixed $resource, ?string $type = null): SymfonyRouteCollection
    {
        $collection = new SymfonyRouteCollection();
        foreach ($this->pool->getAdminServiceCodes() as $code) {
            $admin = $this->pool->getInstance($code);

            foreach ($admin->getRoutes()->getElements() as $route) {
                $name = $route->getDefault('_sonata_name');
                \assert(\is_string($name));
                $collection->add($name, $route);
            }

            $reflection = new \ReflectionObject($admin);
            if (false !== $reflection->getFileName() && file_exists($reflection->getFileName())) {
                $collection->addResource(new FileResource($reflection->getFileName()));
            }
        }

        return $collection;
    }
}
