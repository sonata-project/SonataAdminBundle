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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

class DumpRoutesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this->setName('sonata:admin:dump-routes');
        $this->setDescription('Dump route information to improve performance');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write("Starting dumping cache file");

        $pool = $this->getContainer()->get('sonata.admin.pool');
        $cache = $this->getContainer()->get('sonata.admin.route.cache');

        foreach ($pool->getAdminServiceIds() as $id) {
            $output->writeln(sprintf(' > Generate routes cache for <info>%s</info>', $id));
            $routes = $cache->load($pool->getInstance($id));
            $output->writeln(sprintf('   Load %d routes', count($routes)));
        }

        $output->write("done!");
    }
}
