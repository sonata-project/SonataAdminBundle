<?php

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
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is sent by hook:
 *   - configureQuery.
 *
 * You can register the listener to the event dispatcher by using:
 *   - sonata.admin.event.configure.query
 *   - sonata.admin.event.configure.[admin_code].query  (not implemented yet)
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

    /**
     * @var string
     */
    protected $context;

    /**
     * @param string $context
     */
    public function __construct(AdminInterface $admin, ProxyQueryInterface $proxyQuery, $context)
    {
        $this->admin = $admin;
        $this->proxyQuery = $proxyQuery;
        $this->context = $context;
    }

    /**
     * @return AdminInterface
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return ProxyQueryInterface
     */
    public function getProxyQuery()
    {
        return $this->proxyQuery;
    }
}
