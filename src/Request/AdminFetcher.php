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

namespace Sonata\AdminBundle\Request;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\HttpFoundation\Request;

final class AdminFetcher implements AdminFetcherInterface
{
    /**
     * @var Pool
     */
    private $pool;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    public function get(Request $request): AdminInterface
    {
        $adminCode = (string) $request->get('_sonata_admin');

        if ('' === $adminCode) {
            throw new \InvalidArgumentException(sprintf(
                'There is no `_sonata_admin` defined for the current route `%s`.',
                (string) $request->get('_route')
            ));
        }

        $admin = $this->pool->getAdminByAdminCode($adminCode);

        $rootAdmin = $admin;
        while ($rootAdmin->isChild()) {
            $rootAdmin->setCurrentChild(true);
            $rootAdmin = $rootAdmin->getParent();
        }

        $rootAdmin->setRequest($request);

        if (null !== $request->get('uniqid')) {
            $admin->setUniqId($request->get('uniqid'));
        }

        return $admin;
    }
}
