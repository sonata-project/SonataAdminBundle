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
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
#[AsCommand(name: 'sonata:admin:list', description: 'List all admin services available')]
final class ListAdminCommand extends Command
{
    /**
     * @internal This class should only be used through the console
     */
    public function __construct(
        private Pool $pool,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Admin services:</info>');
        foreach ($this->pool->getAdminServiceCodes() as $code) {
            $instance = $this->pool->getInstance($code);
            $output->writeln(\sprintf(
                '  <info>%-40s</info> %-60s',
                $code,
                $instance->getClass()
            ));
        }

        return 0;
    }
}
