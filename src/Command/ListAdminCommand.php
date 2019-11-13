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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ListAdminCommand extends Command
{
    protected static $defaultName = 'sonata:admin:list';

    /**
     * @var Pool
     */
    private $pool;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;

        parent::__construct();
    }

    public function configure()
    {
        $this->setDescription('List all admin services available');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Admin services:</info>');
        foreach ($this->pool->getAdminServiceIds() as $id) {
            $instance = $this->pool->getInstance($id);
            $output->writeln(sprintf('  <info>%-40s</info> %-60s',
                $id,
                $instance->getClass()
            ));
        }
    }
}
