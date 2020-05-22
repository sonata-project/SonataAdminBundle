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

use Doctrine\Common\Persistence\ManagerRegistry;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Util\ObjectAclManipulatorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class GenerateObjectAclCommand extends QuestionableCommand
{
    protected static $defaultName = 'sonata:admin:generate-object-acl';

    /**
     * @var string
     */
    protected $userEntityClass = '';

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
     * @var RegistryInterface|ManagerRegistry|null
     */
    private $registry;

    /**
     * @param RegistryInterface|ManagerRegistry|null $registry
     */
    public function __construct(Pool $pool, array $aclObjectManipulators, $registry = null)
    {
        $this->pool = $pool;
        $this->aclObjectManipulators = $aclObjectManipulators;
        if (null !== $registry && (!$registry instanceof RegistryInterface && !$registry instanceof ManagerRegistry)) {
            if (!$registry instanceof ManagerRegistry) {
                @trigger_error(sprintf(
                    "Passing an object that doesn't implement %s as argument 3 to %s() is deprecated since sonata-project/admin-bundle 3.56.",
                    ManagerRegistry::class,
                    __METHOD__
                ), E_USER_DEPRECATED);
            }

            throw new \TypeError(sprintf(
                'Argument 3 passed to %s() must be either an instance of %s or %s, %s given.',
                __METHOD__,
                RegistryInterface::class,
                ManagerRegistry::class,
                \is_object($registry) ? \get_class($registry) : \gettype($registry)
            ));
        }
        $this->registry = $registry;

        parent::__construct();
    }

    public function configure()
    {
        $this
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

        if (!$this->registry) {
            $msg = sprintf('The command "%s" has a dependency on a non-existent service "doctrine".', static::$defaultName);

            throw new ServiceNotFoundException('doctrine', static::class, null, [], $msg);
        }

        if ($input->getOption('user_entity')) {
            try {
                $this->getUserEntityClass($input, $output);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

                return 1;
            }
        }

        if (!$this->aclObjectManipulators) {
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
            if (!$manipulator = $this->aclObjectManipulators[$manipulatorId] ?? null) {
                $output->writeln('Admin class is using a manager type that has no manipulator implemented : <info>ignoring</info>');

                continue;
            }
            if (!$manipulator instanceof ObjectAclManipulatorInterface) {
                $output->writeln(sprintf('The interface "ObjectAclManipulatorInterface" is not implemented for %s: <info>ignoring</info>', \get_class($manipulator)));

                continue;
            }

            \assert($admin instanceof AdminInterface);
            $manipulator->batchConfigureAcls($output, $admin, $securityIdentity);
        }

        return 0;
    }

    /**
     * @return string
     */
    protected function getUserEntityClass(InputInterface $input, OutputInterface $output)
    {
        if ('' === $this->userEntityClass) {
            if ($input->getOption('user_entity')) {
                list($userBundle, $userEntity) = Validators::validateEntityName($input->getOption('user_entity'));
            } else {
                list($userBundle, $userEntity) = $this->askAndValidate($input, $output, 'Please enter the User Entity shortcut name: ', '', 'Sonata\AdminBundle\Command\Validators::validateEntityName');
            }
            // Entity exists?
            if ($this->registry instanceof RegistryInterface) {
                $this->userEntityClass = $this->registry->getEntityNamespace($userBundle).'\\'.$userEntity;
            } else {
                $this->userEntityClass = $this->registry->getAliasNamespace($userBundle).'\\'.$userEntity;
            }
        }

        return $this->userEntityClass;
    }
}
