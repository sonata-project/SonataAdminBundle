<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

interface BreadcrumbsBuilderInterface
{
    /**
     * Get breadcrumbs for $action.
     *
     * @param AdminInterface $admin
     * @param string         $action the name of the action we want to get a
     *                               breadcrumbs for
     *
     * @return array the breadcrumbs
     */
    public function getBreadcrumbs($admin, $action);
}
