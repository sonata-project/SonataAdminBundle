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

namespace Sonata\AdminBundle\Tests\App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Tests\App\Controller\InvokableController;

final class AdminAsParameterAdmin extends AbstractAdmin
{
    protected $baseRoutePattern = 'tests/app/admin-as-parameter';
    protected $baseRouteName = 'admin_admin_as_parameter';

    protected function configureRoutes(RouteCollection $collection): void
    {
        // NEXT_MAJOR: Remove this route
        $collection->add('withAnnotation', null, [
            '_controller' => 'Sonata\AdminBundle\Tests\App\Controller\AdminAsParameterController::withAnnotation',
        ]);

        $collection->add('test', null, [
            '_controller' => 'Sonata\AdminBundle\Tests\App\Controller\AdminAsParameterController::test',
        ]);

        $collection->add('invokable', null, [
            '_controller' => InvokableController::class,
        ]);
    }
}
