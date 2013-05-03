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

use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Console\Output\OutputInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

interface ObjectAclManipulatorInterface
{
    /**
     * Batch configure the ACLs for all objects handled by an Admin
     *
     * @abstract
     *
     * @param OutputInterface      $output
     * @param AdminInterface       $admin
     * @param UserSecurityIdentity $securityIdentity
     *
     * @throws \Sonata\AdminBundle\Exception\ModelManagerException
     * @return void
     */
    public function batchConfigureAcls(OutputInterface $output, AdminInterface $admin, UserSecurityIdentity $securityIdentity = null);
}
