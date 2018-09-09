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
final class GetAccessMappingTask implements TaskInterface
{
    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var array
     */
    private $result = [];

    public function __construct(AdminInterface $admin)
    {
        $this->admin = $admin;
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
    public function result()
    {
        return $this->result;
    }

    public function updateResult(array $result)
    {
        $this->result = $result;
    }
}
