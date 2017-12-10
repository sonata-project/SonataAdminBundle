<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Util;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Model\AclInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface AdminAclManipulatorInterface
{
    /**
     * Batch configure the ACLs for all objects handled by an Admin.
     */
    public function configureAcls(OutputInterface $output, AdminInterface $admin);

    /**
     * Add the class ACE's to the admin ACL.
     *
     * @return bool TRUE if admin class ACEs are added, FALSE if not
     */
    public function addAdminClassAces(
        OutputInterface $output,
        AclInterface $acl,
        AclSecurityHandlerInterface $securityHandler,
        array $roleInformation = []
    );
}
