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

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ConfigureActionButtonsTask implements TaskInterface
{
    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var array
     */
    private $list;

    /**
     * @var string
     */
    private $action;

    /**
     * @var object
     */
    private $object;

    /**
     * @var array
     */
    private $result = [];

    /**
     * @param string $action
     * @param object $object
     */
    public function __construct(AdminInterface $admin, array $list, $action, $object)
    {
        $this->admin = $admin;
        $this->list = $this->result = $list;
        $this->action = $action;
        $this->object = $object;
    }

    /**
     * @return AdminInterface
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return array
     */
    public function result()
    {
        return $this->result;
    }

    public function updateResult(array $result)
    {
        $this->result = $result;
    }
}
