<?php

namespace Sonata\AdminBundle\Tests\Fixtures\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * BatchAdminController is used to test relevant batch action
 */
class BatchAdminController extends CRUDController
{
    /**
     * Returns true if $idx contains 123 and 456
     */
    public function fooBatchActionIsRelevant(array $idx, $allElements)
    {
        if (isset($idx[0]) && $idx[0]==123 && isset($idx[1]) && $idx[1]==456) {
            return true;
        }

        if (isset($idx[0]) && $idx[0]==999) {
            return 'flash_foo_error';
        }

        return false;
    }

    public function fooBatchAction(ProxyQueryInterface $query)
    {
    }

    public function barBatchActionIsRelevant(array $idx, $allElements)
    {
        return true;
    }

    public function barBatchAction(ProxyQueryInterface $query=null)
    {
        if ($query === null) {
            return new Response('barBatchAction executed');
        }

        return false;
    }
}
