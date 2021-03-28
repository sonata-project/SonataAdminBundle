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

namespace Sonata\AdminBundle\Tests\Fixtures\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Filter;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;

final class BarFilter extends Filter
{
    public function apply(ProxyQueryInterface $query, array $filterData): void
    {
    }

    public function getDefaultOptions(): array
    {
        return ['bar' => 'bar'];
    }

    public function getRenderSettings(): array
    {
        return [DefaultType::class, [
            'label' => 'label',
        ]];
    }
}
