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

namespace SensioLabs\AdminBundle\Event;

use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is sent by hook:
 *   - configureQuery.
 *
 * You can register the listener to the event dispatcher by using:
 *   - sensiolabs.admin.event.configure.query
 *   - sensiolabs.admin.event.configure.[admin_code].query  (not implemented yet)
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class ConfigureQueryEvent extends Event
{
    /**
     * @param AdminInterface<object>      $admin
     * @param ProxyQueryInterface<object> $proxyQuery
     */
    public function __construct(
        private AdminInterface $admin,
        private ProxyQueryInterface $proxyQuery,
        private string $context,
    ) {
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

    /**
     * @return ProxyQueryInterface<object>
     */
    public function getProxyQuery(): ProxyQueryInterface
    {
        return $this->proxyQuery;
    }
}
