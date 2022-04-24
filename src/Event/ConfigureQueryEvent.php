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
 *
 * @phpstan-template T of object
 */
final class ConfigureQueryEvent extends Event
{
    /**
     * @phpstan-var AdminInterface<T>
     */
    private AdminInterface $admin;

    /**
     * @phpstan-var ProxyQueryInterface<T>
     */
    private ProxyQueryInterface $proxyQuery;

    private string $context;

    /**
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param ProxyQueryInterface<T> $proxyQuery
     */
    public function __construct(AdminInterface $admin, ProxyQueryInterface $proxyQuery, string $context)
    {
        $this->admin = $admin;
        $this->proxyQuery = $proxyQuery;
        $this->context = $context;
    }

    /**
     * @phpstan-return AdminInterface<T>
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
     * @phpstan-return ProxyQueryInterface<T>
     */
    public function getProxyQuery(): ProxyQueryInterface
    {
        return $this->proxyQuery;
    }
}
