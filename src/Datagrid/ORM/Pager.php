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

namespace SensioLabs\AdminBundle\Datagrid\ORM;

use Doctrine\ORM\Tools\Pagination\Paginator;
use SensioLabs\AdminBundle\Datagrid\Pager as BasePager;

/**
 * Doctrine pager class.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 *
 * @phpstan-extends BasePager<ProxyQueryInterface<object>>
 */
final class Pager extends BasePager
{
    private int $resultsCount = 0;

    public function getCurrentPageResults(): iterable
    {
        $query = $this->getQuery();
        if (!$query instanceof ProxyQueryInterface) {
            throw new \TypeError(\sprintf('The pager query MUST implement %s.', ProxyQueryInterface::class));
        }

        $results = $query->execute();

        // We're often both counting and iterating on the current page results.
        // Doing this on the Paginator ends up with two executed queries instead of one.
        // @see https://github.com/sonata-project/SonataAdminBundle/issues/7328#issuecomment-881373378
        if ($results instanceof Paginator) {
            return $results->getIterator();
        }

        return $results;
    }

    public function countResults(): int
    {
        return $this->resultsCount;
    }

    public function init(): void
    {
        $query = $this->getQuery();
        if (!$query instanceof ProxyQueryInterface) {
            throw new \TypeError(\sprintf('The pager query MUST implement %s.', ProxyQueryInterface::class));
        }

        $this->resultsCount = \count($query->execute());

        $query->setFirstResult(null);
        $query->setMaxResults(null);

        if (0 === $this->getPage() || 0 === $this->getMaxPerPage()) {
            $this->setLastPage(0);
        } elseif (0 === $this->countResults()) {
            $this->setLastPage(1);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

            $this->setLastPage((int) ceil($this->countResults() / $this->getMaxPerPage()));

            $query->setFirstResult($offset);
            $query->setMaxResults($this->getMaxPerPage());
        }
    }
}
