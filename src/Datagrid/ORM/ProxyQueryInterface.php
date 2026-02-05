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

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use SensioLabs\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;

/**
 * @phpstan-template-covariant T of object
 * @phpstan-extends BaseProxyQueryInterface<T>
 */
interface ProxyQueryInterface extends BaseProxyQueryInterface
{
    /**
     * @return array<T>|(\Traversable<T>&\Countable)
     */
    public function execute();

    public function getUniqueParameterId(): int;

    /**
     * @param mixed[] $associationMappings
     *
     * @phpstan-return literal-string
     */
    public function entityJoin(array $associationMappings): string;

    public function getQueryBuilder(): QueryBuilder;

    /**
     * This method should be preferred over `$this->getQueryBuilder()->getQuery()`
     * since some changes are done to the query builder in order to handle all the
     * previously called SensioLabs\AdminBundle\Datagrid\ProxyQueryInterface methods.
     */
    public function getDoctrineQuery(): Query;
}
