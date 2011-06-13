<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Security\Handler;

use Sonata\AdminBundle\Admin\AdminInterface;

interface SecurityHandlerInterface
{
    /**
     * @abstract
     * @param string|array $attributes
     * @param null $object
     * @return boolean
     */
    function isGranted($attributes, $object = null);

    /**
     * @abstract
     * @param array $informations
     * @return array
     */
    function buildSecurityInformation(AdminInterface $admin);
}