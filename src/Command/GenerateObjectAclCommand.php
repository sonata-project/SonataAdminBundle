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

use Doctrine\Persistence\ManagerRegistry;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Util\ObjectAclManipulatorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
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
     * NEXT_MAJOR: Rename to `$userModelClass`.
     *
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
     * NEXT_MAJOR: Remove $registry argument.
     *
     * @param RegistryInterface|ManagerRegistry|null $registry
     */
    public function __construct(Pool $pool, array $aclObjectManipulators, $registry = null)
    {
        $this->pool = $pool;
        $this->aclObjectManipulators = $aclObjectManipulators;
        if (null !== $registry) {
            @trigger_error(sprintf(
                'Passing a third argument to %s() is deprecated since sonata-project/admin-bundle 3.77.',
                __METHOD__
            ), E_USER_DEPRECATED);

            if (!$registry instanceof RegistryInterface && !$registry instanceof ManagerRegistry) {
                throw new \TypeError(sprintf(
                    'Argument 3 passed to %s() must be either an instance of %s or %s, %s given.',
                    __METHOD__,
                    RegistryInterface::class,
                    ManagerRegistry::class,
                    \is_object($registry) ? \get_class($registry) : \gettype($registry)
                ));
            }
        }
        $this->registry = $registry;

        parent::__construct();
    }

    /**
     * @internal
     *
     * @param RegistryInterface|ManagerRegistry|null $registry
     */
    public function setRegistry(?object $registry)
    {
        $this->registry = $registry;
    }

    public function configure()
    {
        $this
            ->setDescription('Install ACL for the objects of the Admin Classes.')
            ->addOption('object_owner', null, InputOption::VALUE_OPTIONAL, 'If set, the task will set the object owner for each admin.')
            // NEXT_MAJOR: Remove "user_entity" option.
            ->addOption('user_entity', null, InputOption::VALUE_OPTIONAL, '<error>DEPRECATED</error> Use <comment>user_model</comment> option instead.')
            ->addOption('user_model', null, InputOption::VALUE_OPTIONAL, 'Fully qualified class name <comment>App\Model\User</comment>. If not set, it will be asked the first time an object owner is set.')
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
            'You must use fully qualified class name like <comment>App\Model\User</comment> if you want to set an object owner.',
            '',
        ]);

        if ($input->getOption('user_model')) {
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

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        // NEXT_MAJOR: Remove the following conditional block.
        if (null !== $input->getOption('user_entity')) {
            $output->writeln([
                'Option <comment>user_entity</comment> is deprecated since sonata-project/admin-bundle 3.69 and will be removed in version 4.0.'
                .' Use <comment>user_model</comment> option instead.',
                '',
            ]);

            @trigger_error(
                'Option "user_entity" is deprecated since sonata-project/admin-bundle 3.69 and will be removed in version 4.0.'
                .' Use "user_model" option instead.',
                E_USER_DEPRECATED
            );

            if (null === $input->getOption('user_model')) {
                $input->setOption('user_model', $input->getOption('user_entity'));
            }
        }
    }

    protected function getUserModelClass(InputInterface $input, OutputInterface $output): string
    {
        return $this->getUserEntityClass($input, $output);
    }

    /**
     * NEXT_MAJOR: Remove this method and move its body to `getUserModelClass()`.
     *
     * @deprecated since sonata-project/admin-bundle 3.69. Use `getUserModelClass()` instead.
     *
     * @return string
     */
    protected function getUserEntityClass(InputInterface $input, OutputInterface $output)
    {
        if (self::class !== static::class) {
            @trigger_error(sprintf(
                'Method %s() is deprecated since sonata-project/admin-bundle 3.69 and will be removed in version 4.0.'
                .' Use %s::getUserModelClass() instead.',
                __METHOD__,
                __CLASS__
            ), E_USER_DEPRECATED);
        }

        if ('' === $this->userEntityClass) {
            if ($input->getOption('user_model')) {
                $userModelFromInput = $input->getOption('user_model');
            } else {
                $userModelFromInput = $this->getQuestionHelper()->ask(
                    $input,
                    $output,
                    new Question('Please enter the User Model fully qualified class name: ', '')
                );
            }

            if (!class_exists($userModelFromInput)) {
                // NEXT_MAJOR: Remove the trigger, uncomment the exception and remove the code below the exception
                // until "else".
                @trigger_error(sprintf(
                    'Passing a model shortcut name ("%s" given) as "user_model" option is deprecated since'
                    .' sonata-project/admin-bundle 3.77 and will throw an exception in 4.0.'
                    .' Pass a fully qualified class name instead (e.g. App\Model\User).',
                    $userModelFromInput
                ), E_USER_DEPRECATED);

//                throw new \InvalidArgumentException(sprintf(
//                    'The "user_model" name be a fully qualified class name'
//                    .' ("%s" given, expecting something like App\Model\User)',
//                    $userModelFromInput
//                ));

                if (!$this->registry) {
                    throw new ServiceNotFoundException('doctrine', static::class, null, [], sprintf(
                        'The command "%s" has a dependency on a non-existent service "doctrine".',
                        static::$defaultName
                    ));
                }

                [$userBundle, $userModel] = Validators::validateEntityName($userModelFromInput);

                // Model exists?
                if ($this->registry instanceof RegistryInterface) {
                    $namespace = $this->registry->getEntityNamespace($userBundle);
                } else {
                    $namespace = $this->registry->getAliasNamespace($userBundle);
                }

                $this->userEntityClass = sprintf('%s\%s', $namespace, $userModel);
            } else {
                $this->userEntityClass = $userModelFromInput;
            }
        }

        return $this->userEntityClass;
    }
}
