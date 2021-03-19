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
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Tests\App\Datagrid\Datagrid;
use Symfony\Component\Form\FormFactoryInterface;

final class DatagridBuilder implements DatagridBuilderInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var PagerInterface
     */
    private $pager;

    /**
     * @var ProxyQueryInterface
     */
    private $proxyQuery;

    public function __construct(
        FormFactoryInterface $formFactory,
        PagerInterface $pager,
        ProxyQueryInterface $proxyQuery
    ) {
        $this->formFactory = $formFactory;
        $this->pager = $pager;
        $this->proxyQuery = $proxyQuery;
    }

    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
    }

    public function addFilter(DatagridInterface $datagrid, $type, FieldDescriptionInterface $fieldDescription): void
    {
    }

    public function getBaseDatagrid(AdminInterface $admin, array $values = []): DatagridInterface
    {
        return new Datagrid($this->formFactory, $this->pager, $this->proxyQuery);
    }
}
