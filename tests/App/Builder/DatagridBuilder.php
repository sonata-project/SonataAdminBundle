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

namespace Sonata\AdminBundle\Tests\App\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Tests\App\Datagrid\Datagrid;
use Symfony\Component\Form\FormFactoryInterface;

final class DatagridBuilder implements DatagridBuilderInterface
{
    private $formFactory;
    private $pager;

    public function __construct(FormFactoryInterface $formFactory, PagerInterface $pager)
    {
        $this->formFactory = $formFactory;
        $this->pager = $pager;
    }

    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription): void
    {
    }

    public function addFilter(DatagridInterface $datagrid, $type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin): void
    {
    }

    public function getBaseDatagrid(AdminInterface $admin, array $values = []): DatagridInterface
    {
        return new Datagrid($this->formFactory, $this->pager);
    }
}
