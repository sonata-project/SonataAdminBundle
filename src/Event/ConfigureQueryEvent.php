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

namespace Sonata\AdminBundle\Event;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is sent by hook:
 *   - configureQuery.
 *
 * You can register the listener to the event dispatcher by using:
 *   - sonata.admin.event.configure.query
 *   - sonata.admin.event.configure.[admin_code].query  (not implemented yet)
 *
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ConfigureQueryEvent extends Event
{
    /**
     * @var AdminInterface
     */
    protected $admin;

    /**
     * @var ProxyQueryInterface
     */
    protected $proxyQuery;

    public function __construct(AdminInterface $admin, ProxyQueryInterface $proxyQuery)
    {
        $this->admin = $admin;
        $this->proxyQuery = $proxyQuery;
    }

    /**
     * @return AdminInterface
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @return ProxyQueryInterface
     */
    public function getProxyQuery()
    {
        return $this->proxyQuery;
    }
}
