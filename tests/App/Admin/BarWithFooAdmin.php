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
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Tests\App\Model\Bar;
use Sonata\AdminBundle\Tests\App\Model\Foo;

/**
 * A second admin for bars that shows the foo of a bar as model list field. It is only registered in
 * the "role_security" environment.
 *
 * @phpstan-extends AbstractAdmin<Bar>
 */
final class BarWithFooAdmin extends AbstractAdmin
{
    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'tests/app/bar-with-foo';
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'admin_tests_app_bar_with_foo';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['edit']);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('foo', ModelListType::class, ['class' => Foo::class], ['admin_code' => 'sonata_foo_admin']);
    }
}
