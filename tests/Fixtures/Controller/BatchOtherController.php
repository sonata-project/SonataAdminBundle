<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Fixtures\Controller;

use SensioLabs\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\Response;

final class BatchOtherController
{
    /**
     * @param ProxyQueryInterface<object> $aCustomNameForTheProxyQuery
     */
    public function batchAction(ProxyQueryInterface $aCustomNameForTheProxyQuery): Response
    {
        return new Response('Other Controller');
    }
}
