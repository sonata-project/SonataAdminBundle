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
use Sonata\AdminBundle\Util\AdminAclManipulatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SetupAclCommand extends Command
{
    protected static $defaultName = 'sonata:admin:setup-acl';

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var AdminAclManipulatorInterface
     */
    private $aclManipulator;

    /**
     * @internal This class should only be used through the console
     */
    public function __construct(Pool $pool, AdminAclManipulatorInterface $aclManipulator)
    {
        $this->pool = $pool;
        $this->aclManipulator = $aclManipulator;

        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Install ACL for Admin Classes');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
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

            $this->aclManipulator->configureAcls($output, $admin);
        }

        return 0;
    }
}
