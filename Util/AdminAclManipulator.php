<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Util;

use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;

class AdminAclManipulator implements AdminAclManipulatorInterface
{
    protected $securityHandler;
    protected $maskBuilderClass;

    public function __construct(AclSecurityHandlerInterface $securityHandler, $maskBuilderClass)
    {
        $this->securityHandler = $securityHandler;
        $this->maskBuilderClass =$maskBuilderClass;
    }

    /**
     * {@inheritDoc}
     */
    public function configureAcls(OutputInterface $output, AdminInterface $admin)
    {
        $securityHandler = $admin->getSecurityHandler();
        if (!$securityHandler instanceof AclSecurityHandlerInterface) {
            $output->writeln('Admin class is not configured to use ACL : <info>ignoring</info>');
            return;
        }

        $objectIdentity = ObjectIdentity::fromDomainObject($admin);
        $newAcl = false;
        if (is_null($acl = $this->securityHandler->getObjectAcl($objectIdentity))) {
            $acl = $this->securityHandler->createAcl($objectIdentity);
            $newAcl = true;
        }

        // create admin ACL
        $output->writeln(sprintf(' > install ACL for %s', $admin->getCode()));
        $configResult = $this->addAdminClassAces($output, $acl, $securityHandler->buildSecurityInformation($admin));

        if ($configResult) {
            $this->securityHandler->updateAcl($acl);
        } else {
            $output->writeln(sprintf('   - %s , no roles and permissions found', ($newAcl ? 'skip' : 'removed')));
            $this->securityHandler->deleteAcl($acl);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addAdminClassAces(OutputInterface $output, AclInterface $acl, array $roleInformation = array())
    {
        if (count($this->securityHandler->getAdminPermissions()) > 0 ) {
            $builder = new $this->maskBuilderClass();

            foreach ($roleInformation as $role => $permissions) {
                $aceIndex = $this->securityHandler->findClassAceIndexByRole($acl, $role);
                $roleAdminPermissions = array();

                foreach ($permissions as $permission) {
                    // add only the admin permissions
                    if (in_array($permission, $this->securityHandler->getAdminPermissions())) {
                        $builder->add($permission);
                        $roleAdminPermissions[] = $permission;
                    }
                }

                if (count($roleAdminPermissions) > 0) {
                    if ($aceIndex === false) {
                        $acl->insertClassAce(new RoleSecurityIdentity($role), $builder->get());
                        $action = 'add';
                    } else {
                        $acl->updateClassAce($aceIndex, $builder->get());
                        $action = 'update';
                    }

                    if (!is_null($output)) {
                        $output->writeln(sprintf('   - %s role: %s, permissions: %s', $action, $role, json_encode($roleAdminPermissions)));
                    }

                    $builder->reset();
                } elseif ($aceIndex !== false) {
                    $acl->deleteClassAce($aceIndex);

                    if (!is_null($output)) {
                        $output->writeln(sprintf('   - remove role: %s', $action, $role));
                    }
                }
            }

            return true;
        } else {
            return false;
        }
    }
}