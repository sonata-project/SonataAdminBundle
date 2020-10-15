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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class AdminPoolLoader extends Loader
{
    public const ROUTE_TYPE_NAME = 'sonata_admin';

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var array
     */
    private $adminServiceIds = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(Pool $pool, array $adminServiceIds, ContainerInterface $container)
    {
        $this->pool = $pool;
        $this->adminServiceIds = $adminServiceIds;
        $this->container = $container;
    }

    /**
     * NEXT_MAJOR: Add the ?string param typehint when Symfony 4 support is dropped.
     *
     * @param string        $resource
     * @param string | null $type
     */
    public function supports($resource, $type = null): bool
    {
        return self::ROUTE_TYPE_NAME === $type;
    }

    /**
     * NEXT_MAJOR: Add the ?string param typehint when Symfony 4 support is dropped.
     *
     * @param string       $resource
     * @param string| null $type
     */
    public function load($resource, $type = null): SymfonyRouteCollection
    {
        $collection = new SymfonyRouteCollection();
        foreach ($this->adminServiceIds as $id) {
            $admin = $this->pool->getInstance($id);

            foreach ($admin->getRoutes()->getElements() as $code => $route) {
                $collection->add($route->getDefault('_sonata_name'), $route);
            }

            $reflection = new \ReflectionObject($admin);
            if (file_exists($reflection->getFileName())) {
                $collection->addResource(new FileResource($reflection->getFileName()));
            }
        }

        $reflection = new \ReflectionObject($this->container);
        if (file_exists($reflection->getFileName())) {
            $collection->addResource(new FileResource($reflection->getFileName()));
        }

        return $collection;
    }
}
