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

namespace SensioLabs\AdminBundle\Tests\App\Admin;

use SensioLabs\AdminBundle\Admin\AbstractAdmin;
use SensioLabs\AdminBundle\Route\RouteCollectionInterface;
use SensioLabs\AdminBundle\Tests\App\Controller\InvokableController;

/**
 * @phpstan-extends AbstractAdmin<object>
 */
final class AdminAsParameterAdmin extends AbstractAdmin
{
    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'tests/app/admin-as-parameter';
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'admin_admin_as_parameter';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('test', null, [
            '_controller' => 'SensioLabs\AdminBundle\Tests\App\Controller\AdminAsParameterController::test',
        ]);

        $collection->add('invokable', null, [
            '_controller' => InvokableController::class,
        ]);
    }
}
