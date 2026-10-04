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

namespace Sonata\AdminBundle\Twig;

use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class PagerRuntime implements RuntimeExtensionInterface
{
    /**
     * Returns whether the pager knows the exact number of results.
     *
     * @param PagerInterface<ProxyQueryInterface<object>> $pager
     */
    public function isDeterministic(PagerInterface $pager): bool
    {
        // NEXT_MAJOR: Remove the method_exists check and always call `$pager->isDeterministic()`.
        // @phpstan-ignore-next-line
        if (!method_exists($pager, 'isDeterministic')) {
            return true;
        }

        return $pager->isDeterministic();
    }
}
