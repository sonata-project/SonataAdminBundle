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
use Sonata\AdminBundle\Model\AuditManagerInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class PathInfoBuilder implements RouteBuilderInterface
{
    /**
     * @var AuditManagerInterface
     */
    private $manager;

    public function __construct(AuditManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function build(AdminInterface $admin, RouteCollectionInterface $collection): void
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
            $collection->add('history_compare_revisions', sprintf('%s/history/{baseRevision}/{compareRevision}/compare', $admin->getRouterIdParameter()));
        }

        if ($admin->isAclEnabled()) {
            $collection->add('acl', sprintf('%s/acl', $admin->getRouterIdParameter()));
        }

        // add children urls
        foreach ($admin->getChildren() as $children) {
            $collection->addCollection($children->getRoutes());
        }
    }
}
