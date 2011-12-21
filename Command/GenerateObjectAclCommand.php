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

use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Sonata\AdminBundle\Admin\AdminInterface;

class GenerateObjectAclCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('sonata:admin:generate-object-acl');
        $this->setDescription('Install ACL for the objects of the Admin Classes');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        $output->writeln('Welcome to the AdminBundle object ACL generator');
        $output->writeln(array(
                '',
                'This command helps you generate ACL entities for the objects handled by the AdminBundle.',
                '',
                'Foreach Admin, you will be asked to generate the object ACL entities',
                'You must use the shortcut notation like <comment>AcmeDemoBundle:User</comment> if you want to set an object owner.',
                ''
        ));

        $userEntityClass = '';

        foreach ($this->getContainer()->get('sonata.admin.pool')->getAdminServiceIds() as $id) {

            try {
                $admin = $this->getContainer()->get($id);
            } catch (\Exception $e) {
                $output->writeln('<error>Warning : The admin class cannot be initiated from the command line</error>');
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
                continue;
            }

            if (!$dialog->askConfirmation($output, sprintf("<question>Generate ACLs for the object instances handled by \"%s\"?</question>\n", $id), false)) {
                continue;
            }

            $securityIdentity = null;
            if ($dialog->askConfirmation($output,"<question>Set an object owner?</question>\n", false)) {
                $username = $dialog->askAndValidate($output, 'Please enter the username: ', 'Sonata\AdminBundle\Command\Validators::validateUsername');
                if ($userEntityClass === '') {
                    list($userBundle, $userEntity) = $dialog->askAndValidate($output, 'Please enter the User Entity shortcut name: ', 'Sonata\AdminBundle\Command\Validators::validateEntityName');

                    // Entity exists?
                    try {
                        $userEntityClass = $this->getContainer()->get('doctrine')->getEntityNamespace($userBundle).'\\'.$userEntity;
                    } catch (\Exception $e) {
                        $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
                        continue;
                    }
                }
                $securityIdentity = new UserSecurityIdentity($username, $userEntityClass);
            }

            $manipulatorId = 'sonata.admin.manipulator.acl.object.orm';
            if (!$this->getContainer()->has($manipulatorId)) {
                $output->writeln('Admin class is using a manager type that has no manipulator implemented : <info>ignoring</info>');
                continue;
            }

            $this->getContainer()->get($manipulatorId)->batchConfigureAcls($output, $admin, $securityIdentity);
        }
    }
}