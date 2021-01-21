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
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Controller\ControllerRegistry;
use Sonata\AdminBundle\Model\AuditManagerInterface;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class PathInfoBuilder implements RouteBuilderInterface
{
    /**
     * @var AuditManagerInterface
     */
    protected $manager;

    /**
     * @var ControllerRegistry|null
     */
    private $controllerRegistry;

    public function __construct(AuditManagerInterface $manager, ?ControllerRegistry $controllerRegistry = null)
    {
        $this->manager = $manager;
        $this->controllerRegistry = $controllerRegistry;
    }

    public function create(AdminInterface $admin): RouteCollection
    {
        return new RouteCollection(
            $admin->getBaseCodeRoute(),
            $admin->getBaseRouteName(),
            $admin->getBaseRoutePattern(),
            $this->getBaseControllerName($admin)
        );
    }

    public function build(AdminInterface $admin, RouteCollection $collection)
    {
        $collection->add('list');
        $collection->add('create');
        $collection->add('batch');
        $collection->add('edit', sprintf('%s/edit', $admin->getRouterIdParameter()));
        $collection->add('delete', sprintf('%s/delete', $admin->getRouterIdParameter()));
        $collection->add('show', sprintf('%s/show', $admin->getRouterIdParameter()));
        $collection->add('export');

        if ($this->manager->hasReader($admin->getClass())) {
            $collection->add('history', sprintf('%s/history', $admin->getRouterIdParameter()));
            $collection->add('history_view_revision', sprintf('%s/history/{revision}/view', $admin->getRouterIdParameter()));
            $collection->add('history_compare_revisions', sprintf('%s/history/{base_revision}/{compare_revision}/compare', $admin->getRouterIdParameter()));
        }

        if ($admin->isAclEnabled()) {
            $collection->add('acl', sprintf('%s/acl', $admin->getRouterIdParameter()));
        }

        // add children urls
        foreach ($admin->getChildren() as $children) {
            $collection->addCollection($children->getRoutes());
        }
    }

    private function getBaseControllerName(AdminInterface $admin): string
    {
        if (null === $this->controllerRegistry) {
            return $admin->getBaseControllerName();
        }

        return $this->controllerRegistry->byAdmin($admin);
    }
}
