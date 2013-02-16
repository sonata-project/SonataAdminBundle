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
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;

interface AdminAclManipulatorInterface
{
    /**
     * Batch configure the ACLs for all objects handled by an Admin
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Sonata\AdminBundle\Admin\AdminInterface          $admin
     *
     * @return void
     */
    public function configureAcls(OutputInterface $output, AdminInterface $admin);

    /**
     * Add the class ACE's to the admin ACL
     *
     * @param \Symfony\Component\Console\Output\OutputInterface                $output
     * @param \Symfony\Component\Security\Acl\Model\AclInterface               $acl
     * @param \Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface $securityHandler
     * @param array                                                            $roleInformation
     *
     * @return boolean TRUE if admin class ACEs are added, FALSE if not
     */
    public function addAdminClassAces(OutputInterface $output, AclInterface $acl, AclSecurityHandlerInterface $securityHandler, array $roleInformation = array());
}
