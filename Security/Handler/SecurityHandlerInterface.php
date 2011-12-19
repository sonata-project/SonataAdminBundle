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
    function isGranted(AdminInterface $admin, $attributes, $object = null);

    /**
     * Get a sprintf template to get the role
     *
     * @abstract
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @return string
     */
    function getBaseRole(AdminInterface $admin);

    /**
     * @abstract
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @return void
     */
    function buildSecurityInformation(AdminInterface $admin);

    /**
     * Create object security, fe. make the current user owner of the object
     *
     * @abstract
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param object $object
     * @return void
     */
    function createObjectSecurity(AdminInterface $admin, $object);

    /**
     * Remove object security
     *
     * @abstract
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param object $object
     * @return void
     */
    function deleteObjectSecurity(AdminInterface $admin, $object);
}