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
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AdminPoolLoader extends Loader
{
    public const ROUTE_TYPE_NAME = 'sonata_admin';

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var array
     */
    protected $adminServiceIds = [];

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var ContainerInterface|null
     */
    protected $container;

    // NEXT_MAJOR: Remove $adminServiceIds and $container parameters.
    public function __construct(Pool $pool, array $adminServiceIds = [], ?ContainerInterface $container = null)
    {
        $this->pool = $pool;
        // NEXT_MAJOR: Remove next line.
        if (\func_num_args() > 1) {
            @trigger_error(sprintf(
                'Passing more than one argument to "%s()" is deprecated since'
                .' sonata-project/admin-bundle 3.x.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        // NEXT_MAJOR: Remove the following two lines.
        $this->adminServiceIds = $adminServiceIds;
        $this->container = $container;
    }

    public function supports($resource, $type = null)
    {
        return self::ROUTE_TYPE_NAME === $type;
    }

    public function load($resource, $type = null)
    {
        $collection = new SymfonyRouteCollection();
        // NEXT_MAJOR: Replace $this->getAdminServiceIds() with $this->pool->getAdminServiceIds()
        foreach ($this->getAdminServiceIds() as $id) {
            $admin = $this->pool->getInstance($id);

            foreach ($admin->getRoutes()->getElements() as $code => $route) {
                $collection->add($route->getDefault('_sonata_name'), $route);
            }

            $reflection = new \ReflectionObject($admin);
            if (file_exists($reflection->getFileName())) {
                $collection->addResource(new FileResource($reflection->getFileName()));
            }
        }

        // NEXT_MAJOR: Remove this block.
        if (null !== $this->container) {
            $reflection = new \ReflectionObject($this->container);
            if (file_exists($reflection->getFileName())) {
                $collection->addResource(new FileResource($reflection->getFileName()));
            }
        }

        return $collection;
    }

    /**
     * @return string[]
     */
    private function getAdminServiceIds(): array
    {
        if ([] !== $this->adminServiceIds) {
            return $this->adminServiceIds;
        }

        return $this->pool->getAdminServiceIds();
    }
}
