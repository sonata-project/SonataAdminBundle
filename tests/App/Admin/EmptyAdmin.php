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
use SensioLabs\AdminBundle\Datagrid\ListMapper;
use SensioLabs\AdminBundle\Form\FormMapper;
use SensioLabs\AdminBundle\Show\ShowMapper;

/**
 * @phpstan-extends AbstractAdmin<object>
 */
final class EmptyAdmin extends AbstractAdmin
{
    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'empty';
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'admin_empty';
    }

    protected function configureListFields(ListMapper $list): void
    {
        // Empty
    }

    protected function configureFormFields(FormMapper $form): void
    {
        // Empty
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        // Empty
    }
}
