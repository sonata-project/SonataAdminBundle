<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Sonata\AdminBundle\Route;

use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Model\AuditManagerInterface;

class PathInfoBuilder implements RouteBuilderInterface
{
    protected $manager;

    /**
     * @param \Sonata\AdminBundle\Model\AuditManagerInterface $manager
     */
    public function __construct(AuditManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param \Sonata\AdminBundle\Admin\AdminInterface  $admin
     * @param \Sonata\AdminBundle\Route\RouteCollection $collection
     */
    public function build(AdminInterface $admin, RouteCollection $collection)
    {
        $collection->add('list');
        $collection->add('create');
        $collection->add('batch');
        $collection->add('edit', $admin->getRouterIdParameter().'/edit');
        $collection->add('delete', $admin->getRouterIdParameter().'/delete');
        $collection->add('show', $admin->getRouterIdParameter().'/show');
        $collection->add('export');

        if ($this->manager->hasReader($admin->getClass())) {
            $collection->add('history', $admin->getRouterIdParameter().'/history');
            $collection->add('history_view_revision', $admin->getRouterIdParameter().'/history/{revision}/view');
        }

        // an admin can have only one level of nested child
        if ($admin->getParent()) {
            return;
        }

        // add children urls
        foreach ($admin->getChildren() as $children) {
            $collection->addCollection($children->getRoutes());
        }
    }
}
