<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Command;


use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclAlreadyExistsException;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandler;

class SetupAclCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('sonata:admin:setup-acl');
        $this->setDescription('Install ACL for Admin Classes');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $aclProvider = $this->getContainer()->get('security.acl.provider');

        $output->writeln('Starting ACL AdminBundle configuration');

        foreach ($this->getContainer()->get('sonata.admin.pool')->getAdminServiceIds() as $id) {

            try {
                $admin = $this->getContainer()->get($id);
            } catch (\Exception $e) {
                $output->writeln('<error>Warning : The admin class cannot be initiated from the command line</error>');
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
                continue;
            }

            $securityHandler = $admin->getSecurityHandler();
            if (!$securityHandler instanceof AclSecurityHandler) {
                $output->writeln('Admin class is not configured to use ACL : <info>ignoring</info>');
                continue;
            }

            $objectIdentity = ObjectIdentity::fromDomainObject($admin);
            $newAcl = false;
            try {
                $acl = $aclProvider->findAcl($objectIdentity);
            } catch(AclNotFoundException $e) {
                $acl = $aclProvider->createAcl($objectIdentity);
                $newAcl = true;
            }

            // create admin ACL
            $output->writeln(sprintf(' > install ACL for %s', $id));
            $configResult = $securityHandler->addAdminClassAces($acl, $securityHandler->buildSecurityInformation($admin), $output);

            if ($configResult) {
                $aclProvider->updateAcl($acl);
            } elseif ($aclProvider instanceof MutableAclProviderInterface) {
                $output->writeln(sprintf('   - %s , no roles and permissions found', ($newAcl ? 'skip' : 'removed')));
                $aclProvider->deleteAcl($objectIdentity);
            }
        }
    }
}