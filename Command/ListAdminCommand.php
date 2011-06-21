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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

class ListAdminCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this->setName('sonata:admin:list');
        $this->setDescription('List all admin services available');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $pool = $this->getContainer()->get('sonata.admin.pool');

        $output->writeln("<info>Admin services:</info>");
        foreach ($pool->getAdminServiceIds() as $id) {
            $instance = $this->getContainer()->get($id);
            $output->writeln(sprintf("  <info>%-40s</info> %-60s",
                $id,
                $instance->getClass()
            ));
        }
    }
}