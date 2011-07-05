<?php
/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Datagrid;


/**
 * Interface used by the Datagrid to build the query
 */
interface ProxyQueryInterface
{
    function execute(array $params = array(), $hydrationMode = null);

    function __call($name, $args);

    function setSortBy($sortBy);

    function getSortBy();

    function setSortOrder($sortOrder);

    function getSortOrder();

    function getSingleScalarResult();

    function setFirstResult($firstResult);

    function getFirstResult();

    function setMaxResults($maxResults);

    function getMaxResults();
}
