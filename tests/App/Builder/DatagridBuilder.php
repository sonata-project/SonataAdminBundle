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
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @psalm-suppress DeprecatedInterface
 *
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
