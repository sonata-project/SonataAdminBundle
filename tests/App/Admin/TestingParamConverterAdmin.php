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

final class TestingParamConverterAdmin extends AbstractAdmin
{
    protected $baseRoutePattern = 'tests/app/testing-param-converter';
    protected $baseRouteName = 'admin_testing_param_converter';

    protected function configureRoutes(RouteCollection $collection): void
    {
        $collection->add('withAnnotation', null, [
            '_controller' => 'Sonata\AdminBundle\Tests\App\Controller\ParamConverterController::withAnnotation',
        ]);

        $collection->add('withoutAnnotation', null, [
            '_controller' => 'Sonata\AdminBundle\Tests\App\Controller\ParamConverterController::withoutAnnotation',
        ]);

        $collection->add('invokable', null, [
            '_controller' => InvokableController::class,
        ]);
    }
}
