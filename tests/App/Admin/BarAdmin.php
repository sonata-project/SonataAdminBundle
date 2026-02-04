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
use SensioLabs\AdminBundle\Tests\App\Model\Bar;

/**
 * @phpstan-extends AbstractAdmin<Bar>
 */
final class BarAdmin extends AbstractAdmin
{
    protected function createNewInstance(): object
    {
        return new Bar('test_id');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->add('id');
    }

    protected function configureFormFields(FormMapper $form): void
    {
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('id');
    }
}
