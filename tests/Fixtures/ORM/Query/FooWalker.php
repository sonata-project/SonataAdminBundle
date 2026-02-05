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

namespace Sonata\DoctrineORMAdminBundle\Tests\Fixtures\Query;

use Doctrine\ORM\Query\AST\OrderByClause;
use Doctrine\ORM\Query\SqlOutputWalker;

final class FooWalker extends SqlOutputWalker
{
    /**
     * @param OrderByClause $orderByClause
     */
    public function walkOrderByClause($orderByClause): string
    {
        return str_replace(' ASC', ' DESC', parent::walkOrderByClause($orderByClause));
    }
}
