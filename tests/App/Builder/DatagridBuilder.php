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

namespace SensioLabs\AdminBundle\Tests\App\Builder;

use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Builder\DatagridBuilderInterface;
use SensioLabs\AdminBundle\Datagrid\Datagrid;
use SensioLabs\AdminBundle\Datagrid\DatagridInterface;
use SensioLabs\AdminBundle\Datagrid\PagerInterface;
use SensioLabs\AdminBundle\Datagrid\ProxyQueryInterface;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionCollection;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @phpstan-implements DatagridBuilderInterface<ProxyQueryInterface<object>>
 */
final class DatagridBuilder implements DatagridBuilderInterface
{
    /**
     * @param PagerInterface<ProxyQueryInterface<object>> $pager
     * @param ProxyQueryInterface<object>                 $proxyQuery
     */
    public function __construct(
        private FormFactoryInterface $formFactory,
        private PagerInterface $pager,
        private ProxyQueryInterface $proxyQuery,
    ) {
    }

    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
    }

    public function addFilter(DatagridInterface $datagrid, ?string $type, FieldDescriptionInterface $fieldDescription): void
    {
    }

    public function getBaseDatagrid(AdminInterface $admin, array $values = []): DatagridInterface
    {
        return new Datagrid(
            $this->proxyQuery,
            new FieldDescriptionCollection(),
            $this->pager,
            $this->formFactory->createNamedBuilder('filter', FormType::class, [])
        );
    }
}
