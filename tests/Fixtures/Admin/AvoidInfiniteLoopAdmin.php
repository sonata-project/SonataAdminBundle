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

namespace SensioLabs\AdminBundle\Tests\Fixtures\Admin;

use SensioLabs\AdminBundle\Admin\AbstractAdmin;
use SensioLabs\AdminBundle\Datagrid\DatagridMapper;
use SensioLabs\AdminBundle\Datagrid\ListMapper;
use SensioLabs\AdminBundle\Form\FormMapper;
use SensioLabs\AdminBundle\Show\ShowMapper;

/**
 * @phpstan-extends AbstractAdmin<object>
 */
final class AvoidInfiniteLoopAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $this->getFilterFieldDescriptions();
        $this->hasFilterFieldDescription('help');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $this->getListFieldDescriptions();
        $this->hasFilterFieldDescription('help');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $this->getFormFieldDescriptions();
        $this->hasFilterFieldDescription('help');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $this->getShowFieldDescriptions();
        $this->hasFilterFieldDescription('help');
    }
}
