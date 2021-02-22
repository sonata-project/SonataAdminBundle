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

namespace Sonata\AdminBundle\Tests\Fixtures\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * BatchAdminController is used to test relevant batch action.
 */
class BatchAdminController extends CRUDController
{
    /**
     * Returns true if $idx contains 123 and 456.
     */
    public function batchActionFooIsRelevant(array $idx, $allElements)
    {
        if (isset($idx[0], $idx[1]) && '123' === $idx[0] && '456' === $idx[1]) {
            return true;
        }

        if (isset($idx[0]) && '999' === $idx[0]) {
            return 'flash_foo_error';
        }

        return false;
    }

    public function batchActionFoo(ProxyQueryInterface $query): void
    {
    }

    public function batchActionBarIsRelevant(array $idx, $allElements)
    {
        return true;
    }

    public function batchActionBar(?ProxyQueryInterface $query = null)
    {
        if (null === $query) {
            return new Response('batchActionBar executed');
        }

        return false;
    }

    public function batchActionFooBarIsRelevant(array $idx, $allElements)
    {
    }
}
