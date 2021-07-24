<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Command;

use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Util\ObjectAclManipulatorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class GenerateObjectAclCommand extends QuestionableCommand
{
    protected static $defaultName = 'sonata:admin:generate-object-acl';

    /**
     * @var string
     */
    private $userModelClass = '';

    /**
     * @var Pool
     */
    private $pool;

    /**
     * An array of object ACL manipulators indexed by their service ids.
     *
     * @var ObjectAclManipulatorInterface[]
     */
    private $aclObjectManipulators = [];

    /**
     * @param ObjectAclManipulatorInterface[] $aclObjectManipulators
     *
     * @internal This class should only be used through the console
     */
    public function __construct(Pool $pool, array $aclObjectManipulators)
    {
        $this->pool = $pool;
        $this->aclObjectManipulators = $aclObjectManipulators;

        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Install ACL for the objects of the Admin Classes.')
            ->addOption('object_owner', null, InputOption::VALUE_OPTIONAL, 'If set, the task will set the object owner for each admin.')
            ->addOption('user_model', null, InputOption::VALUE_OPTIONAL, 'Fully qualified class name <comment>App\Model\User</comment>. If not set, it will be asked the first time an object owner is set.')
            ->addOption('step', null, InputOption::VALUE_NONE, 'If set, the task will ask for each admin if the ACLs need to be generated and what object owner to set, if any.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Welcome to the AdminBundle object ACL generator');
        $output->writeln([
            '',
            'This command helps you to generate ACL entities for the objects handled by the AdminBundle.',
            '',
            'If the step option is used, you will be asked if you want to generate the object ACL entities for each Admin.',
            'You must use fully qualified class name like <comment>App\Model\User</comment> if you want to set an object owner.',
            '',
        ]);

        if (null !== $input->getOption('user_model')) {
            try {
                $this->getUserModelClass($input, $output);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

                return 1;
            }
        }

        if ([] === $this->aclObjectManipulators) {
            $output->writeln('No manipulators are implemented : <info>ignoring</info>');

            return 1;
        }

        foreach ($this->pool->getAdminServiceIds() as $id) {
            try {
                $admin = $this->pool->getInstance($id);
            } catch (\Exception $e) {
                $output->writeln('<error>Warning : The admin class cannot be initiated from the command line</error>');
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

                continue;
            }

            if ($input->getOption('step') && !$this->askConfirmation($input, $output, sprintf("<question>Generate ACLs for the object instances handled by \"%s\"?</question>\n", $id), 'no')) {
                continue;
            }

            $securityIdentity = null;
            if ($input->getOption('step') && $this->askConfirmation($input, $output, "<question>Set an object owner?</question>\n", 'no')) {
                $username = $this->askAndValidate($input, $output, 'Please enter the username: ', '', [Validators::class, 'validateUsername']);

                $securityIdentity = new UserSecurityIdentity($username, $this->getUserModelClass($input, $output));
            }

            $objectOwner = $input->getOption('object_owner');
            if (!$input->getOption('step') && null !== $objectOwner) {
                $securityIdentity = new UserSecurityIdentity($objectOwner, $this->getUserModelClass($input, $output));
            }

            $manipulatorId = sprintf('sonata.admin.manipulator.acl.object.%s', $admin->getManagerType());
            if (!isset($this->aclObjectManipulators[$manipulatorId])) {
                $output->writeln('Admin class is using a manager type that has no manipulator implemented : <info>ignoring</info>');

                continue;
            }

            $this->aclObjectManipulators[$manipulatorId]->batchConfigureAcls($output, $admin, $securityIdentity);
        }

        return 0;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
    }

    private function getUserModelClass(InputInterface $input, OutputInterface $output): string
    {
        if ('' === $this->userModelClass) {
            $userModel = $input->getOption('user_model');
            if (null !== $userModel) {
                $userModelFromInput = $userModel;
            } else {
                $userModelFromInput = $this->getQuestionHelper()->ask(
                    $input,
                    $output,
                    new Question('Please enter the User Model fully qualified class name: ', '')
                );
            }

            if (!class_exists($userModelFromInput)) {
                throw new \InvalidArgumentException(sprintf(
                    'The "user_model" name be a fully qualified class name'
                    .' ("%s" given, expecting something like App\Model\User)',
                    $userModelFromInput
                ));
            }

            $this->userModelClass = $userModelFromInput;
        }

        return $this->userModelClass;
    }
}
