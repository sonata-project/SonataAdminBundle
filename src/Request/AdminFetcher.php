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

namespace SensioLabs\AdminBundle\Request;

use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\BCLayer\BCHelper;
use Symfony\Component\HttpFoundation\Request;

final class AdminFetcher implements AdminFetcherInterface
{
    public function __construct(
        private Pool $pool,
    ) {
    }

    public function get(Request $request): AdminInterface
    {
        $adminCode = BCHelper::getFromRequest($request, '_sonata_admin');

        if (!\is_string($adminCode)) {
            $route = BCHelper::getFromRequest($request, '_route', '');
            \assert(\is_string($route));

            throw new \InvalidArgumentException(\sprintf(
                'There is no `_sonata_admin` defined for the current route `%s`.',
                $route
            ));
        }

        $admin = $this->pool->getAdminByAdminCode($adminCode);

        $rootAdmin = $admin;
        while ($rootAdmin->isChild()) {
            $rootAdmin->setCurrentChild(true);
            $rootAdmin = $rootAdmin->getParent();
        }

        $rootAdmin->setRequest($request);

        if (\is_string(BCHelper::getFromRequest($request, 'uniqid'))) {
            $admin->setUniqId(BCHelper::getFromRequest($request, 'uniqid'));
        }

        return $admin;
    }
}
