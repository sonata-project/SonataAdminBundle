<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Builder;

use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Route\RouteCollectionInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface RouteBuilderInterface
{
    /**
     * @param AdminInterface<object> $admin
     */
    public function build(AdminInterface $admin, RouteCollectionInterface $collection): void;
}
