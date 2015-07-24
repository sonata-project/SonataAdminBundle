<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;

interface RouteBuilderInterface
{
    /**
     * @param \Sonata\AdminBundle\Admin\AdminInterface  $admin
     * @param \Sonata\AdminBundle\Route\RouteCollection $collection
     */
    public function build(AdminInterface $admin, RouteCollection $collection);
}
