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
use Sonata\AdminBundle\Mapper\MapperInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is sent by hook:
 *   - configureFormFields
 *   - configureListFields
 *   - configureDatagridFilters
 *   - configureShowFields.
 *
 * You can register the listener to the event dispatcher by using:
 *   - sonata.admin.event.configure.[form|list|datagrid|show]
 *   - sonata.admin.event.configure.[admin_code].[form|list|datagrid|show] (not implemented yet)
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class ConfigureEvent extends Event
{
    public const TYPE_SHOW = 'show';
    public const TYPE_DATAGRID = 'datagrid';
    public const TYPE_FORM = 'form';
    public const TYPE_LIST = 'list';

    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var MapperInterface
     */
    private $mapper;

    /**
     * @var string
     */
    private $type;

    public function __construct(AdminInterface $admin, MapperInterface $mapper, string $type)
    {
        $this->admin = $admin;
        $this->mapper = $mapper;
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAdmin(): AdminInterface
    {
        return $this->admin;
    }

    public function getMapper(): MapperInterface
    {
        return $this->mapper;
    }
}
