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

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Util\AdminAclManipulatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SetupAclCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'sonata:admin:setup-acl';

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var AdminAclManipulatorInterface
     */
    private $aclManipulator;

    public function __construct(Pool $pool, AdminAclManipulatorInterface $aclManipulator)
    {
        $this->pool = $pool;
        $this->aclManipulator = $aclManipulator;

        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Install ACL for Admin Classes')
            ->setName(static::$defaultName)// BC for symfony/console < 3.4.0
            // NEXT_MAJOR: drop this line after drop support symfony/console < 3.4.0
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('Starting ACL AdminBundle configuration');

        foreach ($this->pool->getAdminServiceIds() as $id) {
            try {
                $admin = $this->pool->getInstance($id);
            } catch (\Exception $e) {
                $output->writeln('<error>Warning : The admin class cannot be initiated from the command line</error>');
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

                continue;
            }

            \assert($admin instanceof AdminInterface);
            $this->aclManipulator->configureAcls($output, $admin);
        }
    }
}
