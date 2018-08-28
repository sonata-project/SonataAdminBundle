<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Command;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Util\ObjectAclManipulatorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class GenerateObjectAclCommand extends QuestionableCommand
{
    /**
     * @var string
     */
    protected $userEntityClass = '';

    public function configure()
    {
        $this
            ->setName('sonata:admin:generate-object-acl')
            ->setDescription('Install ACL for the objects of the Admin Classes.')
            ->addOption('object_owner', null, InputOption::VALUE_OPTIONAL, 'If set, the task will set the object owner for each admin.')
            ->addOption('user_entity', null, InputOption::VALUE_OPTIONAL, 'Shortcut notation like <comment>AcmeDemoBundle:User</comment>. If not set, it will be asked the first time an object owner is set.')
            ->addOption('step', null, InputOption::VALUE_NONE, 'If set, the task will ask for each admin if the ACLs need to be generated and what object owner to set, if any.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Welcome to the AdminBundle object ACL generator');
        $output->writeln([
                '',
                'This command helps you to generate ACL entities for the objects handled by the AdminBundle.',
                '',
                'If the step option is used, you will be asked if you want to generate the object ACL entities for each Admin.',
                'You must use the shortcut notation like <comment>AcmeDemoBundle:User</comment> if you want to set an object owner.',
                '',
        ]);

        if ($input->getOption('user_entity')) {
            try {
                $this->getUserEntityClass($input, $output);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

                return;
            }
        }

        foreach ($this->getContainer()->get('sonata.admin.pool')->getAdminServiceIds() as $id) {
            try {
                $admin = $this->getContainer()->get($id);
            } catch (\Exception $e) {
                $output->writeln('<error>Warning : The admin class cannot be initiated from the command line</error>');
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

                continue;
            }

            if ($input->getOption('step') && !$this->askConfirmation($input, $output, sprintf("<question>Generate ACLs for the object instances handled by \"%s\"?</question>\n", $id), 'no', '?')) {
                continue;
            }

            $securityIdentity = null;
            if ($input->getOption('step') && $this->askConfirmation($input, $output, "<question>Set an object owner?</question>\n", 'no', '?')) {
                $username = $this->askAndValidate($input, $output, 'Please enter the username: ', '', 'Sonata\AdminBundle\Command\Validators::validateUsername');

                $securityIdentity = new UserSecurityIdentity($username, $this->getUserEntityClass($input, $output));
            }
            if (!$input->getOption('step') && $input->getOption('object_owner')) {
                $securityIdentity = new UserSecurityIdentity($input->getOption('object_owner'), $this->getUserEntityClass($input, $output));
            }

            $manipulatorId = sprintf('sonata.admin.manipulator.acl.object.%s', $admin->getManagerType());
            if (!$this->getContainer()->has($manipulatorId)) {
                $output->writeln('Admin class is using a manager type that has no manipulator implemented : <info>ignoring</info>');

                continue;
            }
            $manipulator = $this->getContainer()->get($manipulatorId);
            if (!$manipulator instanceof ObjectAclManipulatorInterface) {
                $output->writeln(sprintf('The interface "ObjectAclManipulatorInterface" is not implemented for %s: <info>ignoring</info>', \get_class($manipulator)));

                continue;
            }

            \assert($admin instanceof AdminInterface);
            $manipulator->batchConfigureAcls($output, $admin, $securityIdentity);
        }
    }

    /**
     * @return string
     */
    protected function getUserEntityClass(InputInterface $input, OutputInterface $output)
    {
        if ('' === $this->userEntityClass) {
            if ($input->getOption('user_entity')) {
                list($userBundle, $userEntity) = Validators::validateEntityName($input->getOption('user_entity'));
                $this->userEntityClass = $this->getContainer()->get('doctrine')->getEntityNamespace($userBundle).'\\'.$userEntity;
            } else {
                list($userBundle, $userEntity) = $this->askAndValidate($input, $output, 'Please enter the User Entity shortcut name: ', '', 'Sonata\AdminBundle\Command\Validators::validateEntityName');

                // Entity exists?
                $this->userEntityClass = $this->getContainer()->get('doctrine')->getEntityNamespace($userBundle).'\\'.$userEntity;
            }
        }

        return $this->userEntityClass;
    }
}
