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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class ConfigureQueryEvent extends Event
{
    /**
     * @var AdminInterface<object>
     */
    private $admin;

    /**
     * @var ProxyQueryInterface
     */
    private $proxyQuery;

    /**
     * @var string
     */
    private $context;

    /**
     * @param AdminInterface<object> $admin
     */
    public function __construct(AdminInterface $admin, ProxyQueryInterface $proxyQuery, string $context)
    {
        $this->admin = $admin;
        $this->proxyQuery = $proxyQuery;
        $this->context = $context;
    }

    /**
     * @return AdminInterface<object>
     */
    public function getAdmin(): AdminInterface
    {
        return $this->admin;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function getProxyQuery(): ProxyQueryInterface
    {
        return $this->proxyQuery;
    }
}
