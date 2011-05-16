Doctrine ORM Proxy Query
========================


The ``ProxyQuery`` object is used to add missing features from the original Doctrine Query builder :

  - ``execute`` method - no need to call the ``getQuery()`` method
  - add sort by and sort order options
  - add preselect id query on left join query, so a limit query will be only apply on the left statement and
    not on the full select statement. This simulate the original Doctrine 1 behavior.


.. code-block:: php

    <?php
    use Sonata\AdminBundle\Datagrid\ORM\ProxyQuery;

    $queryBuilder = $this->em->createQueryBuilder();
    $queryBuilder->from('Post', 'p');

    $proxyQuery = new ProxyQuery($queryBuilder);
    $proxyQuery->leftJoin('p.tags', t);
    $proxyQuery->setSortBy('name');
    $proxyQuery->setMaxResults(10);


    $results = $proxyQuery->execute();