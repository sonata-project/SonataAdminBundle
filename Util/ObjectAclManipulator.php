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

use Symfony\Component\Console\Output\OutputInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;

abstract class ObjectAclManipulator implements ObjectAclManipulatorInterface
{
    protected $securityHandler;

    public function __construct(AclSecurityHandlerInterface $securityHandler)
    {
        $this->securityHandler = $securityHandler;
    }

    /**
     * Configure the object ACL for the passed object identities
     *
     * @param AdminInterface $admin
     * @param array $oids an array of ObjectIdentityInterface implementations
     * @param UserSecurityIdentity $securityIdentity
     * @throws \Exception
     * @return array [countAdded, countUpdated]
     */
    public function configureAcls(AdminInterface $admin, array $oids, UserSecurityIdentity $securityIdentity = null)
    {
        $countAdded = 0;
        $countUpdated = 0;

        $acls = $this->securityHandler->findObjectAcls($oids);

        foreach ($oids as $oid) {
            if ($acls->contains($oid)) {
                $acl = $acls->offsetGet($oid);
                $countUpdated++;
            } else {
                $acl = $this->securityHandler->createAcl($oid);
                $countAdded++;
            }

            if (!is_null($securityIdentity)) {
                // add object owner
                $this->securityHandler->addObjectOwner($acl, $securityIdentity);
            }

            $this->securityHandler->addObjectClassAces($acl, $this->securityHandler->buildSecurityInformation($admin));
            $this->securityHandler->updateAcl($acl);
        }

        return array($countAdded, $countUpdated);
    }
}