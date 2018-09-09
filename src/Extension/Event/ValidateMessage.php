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
use Sonata\CoreBundle\Validator\ErrorElement;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ValidateMessage implements MessageInterface
{
    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var ErrorElement
     */
    private $errorElement;

    /**
     * @var object
     */
    private $object;

    /**
     * @param object $object
     */
    public function __construct(AdminInterface $admin, ErrorElement $errorElement, $object)
    {
        $this->admin = $admin;
        $this->errorElement = $errorElement;
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
     * @return ErrorElement
     */
    public function getErrorElement()
    {
        return $this->errorElement;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }
}
