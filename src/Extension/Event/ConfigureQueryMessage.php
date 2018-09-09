<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Extension\Event;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ConfigureQueryMessage implements MessageInterface
{
    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var ProxyQueryInterface
     */
    private $query;

    /**
     * @var string
     */
    private $context;

    /**
     * @param string $context
     */
    public function __construct(AdminInterface $admin, ProxyQueryInterface $query, $context)
    {
        $this->admin = $admin;
        $this->query = $query;
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
     * @return ProxyQueryInterface
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }
}
