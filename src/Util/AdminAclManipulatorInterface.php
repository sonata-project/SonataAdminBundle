<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Util;

use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface AdminAclManipulatorInterface
{
    /**
     * Batch configure the ACLs for all objects handled by an Admin.
     *
     * @param AdminInterface<object> $admin
     */
    public function configureAcls(OutputInterface $output, AdminInterface $admin): void;

    /**
     * Add the class ACE's to the admin ACL.
     *
     * @param AdminInterface<object> $admin
     *
     * @return bool TRUE if admin class ACEs are added, FALSE if not
     */
    public function addAdminClassAces(
        OutputInterface $output,
        MutableAclInterface $acl,
        AclSecurityHandlerInterface $securityHandler,
        AdminInterface $admin,
    ): bool;
}
