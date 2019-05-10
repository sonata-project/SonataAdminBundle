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
use Sonata\AdminBundle\Filter\FilterBag;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is dispatched by hook "configureFilterParameters".
 *
 * You can listen this event by subscribing to "sonata.admin.event.configure.filter_parameters" event
 *
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class ConfigureFilterParametersEvent extends Event
{
    public const EVENT_FILTER_PARAMETERS = 'sonata.admin.event.configure.filter_parameters';

    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var FilterBag
     */
    private $filterBag;

    public function __construct(AdminInterface $admin, FilterBag $filterBag)
    {
        $this->admin = $admin;
        $this->filterBag = $filterBag;
    }

    public function getAdmin(): AdminInterface
    {
        return $this->admin;
    }

    public function getFilterBag(): FilterBag
    {
        return $this->filterBag;
    }
}
