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

class FooFilter extends Filter
{
    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function filter(ProxyQueryInterface $query, $alias, $field, $value): void
    {
    }

    public function apply($query, $value): void
    {
    }

    public function callSetActive(bool $active): void
    {
        $this->setActive($active);
    }

    public function getDefaultOptions()
    {
        return [
            'foo' => 'bar',
        ];
    }

    public function getRenderSettings(): void
    {
    }
}
