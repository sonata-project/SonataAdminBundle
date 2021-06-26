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
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is sent by hook:
 *   - preUpdate | postUpdate
 *   - prePersist | postPersist
 *   - preRemove | postRemove.
 *
 * You can register the listener to the event dispatcher by using:
 *   - sonata.admin.event.persistence.[pre|post]_[persist|update|remove)
 *   - sonata.admin.event.persistence.[admin_code].[pre|post]_[persist|update|remove)  (not implemented yet)
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of object
 */
final class PersistenceEvent extends Event
{
    public const TYPE_PRE_UPDATE = 'pre_update';
    public const TYPE_POST_UPDATE = 'post_update';
    public const TYPE_PRE_PERSIST = 'pre_persist';
    public const TYPE_POST_PERSIST = 'post_persist';
    public const TYPE_PRE_REMOVE = 'pre_remove';
    public const TYPE_POST_REMOVE = 'post_remove';

    /**
     * @var AdminInterface<object>
     * @phpstan-var AdminInterface<T>
     */
    private $admin;

    /**
     * @var object
     * @phpstan-var T
     */
    private $object;

    /**
     * @var string
     */
    private $type;

    /**
     * @phpstan-param AdminInterface<T> $admin
     * @phpstan-param T $object
     */
    public function __construct(AdminInterface $admin, object $object, string $type)
    {
        $this->admin = $admin;
        $this->object = $object;
        $this->type = $type;
    }

    /**
     * @phpstan-return AdminInterface<T>
     */
    public function getAdmin(): AdminInterface
    {
        return $this->admin;
    }

    /**
     * @phpstan-return T
     */
    public function getObject(): object
    {
        return $this->object;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
