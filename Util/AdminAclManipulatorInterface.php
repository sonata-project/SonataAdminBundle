<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Util;

use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

interface AdminAclManipulatorInterface
{
    /**
     * Batch configure the ACLs for all objects handled by an Admin
     *
     * @abstract
     * @param OutputInterface $output
     * @param AdminInterface $admin
     * @param UserSecurityIdentity $securityIdentity
     * @throws ModelManagerException
     * @return void
     */
    function configureAcls(OutputInterface $output, AdminInterface $admin);

    /**
     * Add the class ACE's to the admin ACL
     *
     * @abstract
     * @param AclInterface $acl
     * @param array $roleInformation
     * @param OutputInterface $output
     * @return boolean TRUE if admin class ACEs are added, FALSE if not
     */
    function addAdminClassAces(OutputInterface $output, AclInterface $acl, array $roleInformation = array());
}