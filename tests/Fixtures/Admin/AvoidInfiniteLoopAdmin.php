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

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

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
