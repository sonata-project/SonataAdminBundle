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
use Symfony\Component\ClassLoader\ClassCollectionLoader;

class CreateClassCacheCommand extends ContainerAwareCommand
{

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this->setName('cache:create-cache-class');
        $this->setDescription('Generate the classes.php files');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $kernel = $this->getContainer()->get('kernel');

        $classmap = $kernel->getCacheDir().'/classes.map';

        if (!is_file($classmap)) {
            throw new \RuntimeException(sprintf('The file %s does not exist', $classmap));
        }

        $name = 'classes';
        $extension = '.php';

        $output->write("<info>Writing cache file ...</info>");
        ClassCollectionLoader::load(
            include($classmap),
            $kernel->getCacheDir(),
            $name,
            $kernel->isDebug(),
            false,
            $extension
        );

        $output->writeln(" done!");
    }
}
