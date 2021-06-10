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

namespace Sonata\AdminBundle\Tests\App\Datagrid;

use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Filter\FilterInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class Datagrid.
 *
 * @phpstan-implements DatagridInterface<ProxyQueryInterface>
 */
final class Datagrid implements DatagridInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var PagerInterface<ProxyQueryInterface>
     */
    private $pager;

    /**
     * @var ProxyQueryInterface
     */
    private $proxyQuery;

    /**
     * @param PagerInterface<ProxyQueryInterface> $pager
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        PagerInterface $pager,
        ProxyQueryInterface $proxyQuery
    ) {
        $this->formFactory = $formFactory;
        $this->pager = $pager;
        $this->proxyQuery = $proxyQuery;
    }

    public function getPager(): PagerInterface
    {
        return $this->pager;
    }

    public function getQuery(): ProxyQueryInterface
    {
        return $this->proxyQuery;
    }

    public function getResults(): iterable
    {
        return $this->pager->getCurrentPageResults();
    }

    public function buildPager(): void
    {
    }

    public function addFilter(FilterInterface $filter): FilterInterface
    {
        return $filter;
    }

    public function getFilters(): array
    {
        return [];
    }

    public function reorderFilters(array $keys): void
    {
    }

    public function getValues(): array
    {
        return [];
    }

    /**
     * @return FieldDescriptionCollection<FieldDescriptionInterface>
     */
    public function getColumns(): FieldDescriptionCollection
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function setValue(string $name, ?string $operator, $value): void
    {
    }

    public function getForm(): FormInterface
    {
        return $this->formFactory->createNamedBuilder('filter', FormType::class, [])->getForm();
    }

    public function getFilter(string $name): FilterInterface
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function hasFilter(string $name): bool
    {
        return false;
    }

    public function removeFilter(string $name): void
    {
    }

    public function hasActiveFilters(): bool
    {
        return false;
    }

    public function hasDisplayableFilters(): bool
    {
        return false;
    }

    public function getSortParameters(FieldDescriptionInterface $fieldDescription): array
    {
        return ['filter' => []];
    }

    public function getPaginationParameters(int $page): array
    {
        return ['filter' => []];
    }
}
