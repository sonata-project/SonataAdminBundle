<?php

declare(strict_types=1);

namespace Sonata\AdminBundle\Tests\Fixtures\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

final class AvoidInfiniteLoopAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $this->getFilterFieldDescriptions();
        $this->hasFilterFieldDescription('help');
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $this->getListFieldDescriptions();
        $this->hasFilterFieldDescription('help');
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $this->getFormFieldDescriptions();
        $this->hasFilterFieldDescription('help');
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $this->getShowFieldDescriptions();
        $this->hasFilterFieldDescription('help');
    }
}
