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

use Symfony\Component\ClassLoader\ClassCollectionLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

@trigger_error(
    'The '.__NAMESPACE__.'\CreateClassCacheCommand class is deprecated since version 3.39.0 and will be removed in 4.0.',
    E_USER_DEPRECATED
);

/**
 * NEXT_MAJOR: Remove this class.
 *
 * @deprecated since version sonata-project/admin-bundle 3.39.0 and will be removed in 4.0.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CreateClassCacheCommand extends Command
{
    protected static $defaultName = 'cache:create-cache-class';

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var bool
     */
    private $debug;

    public function __construct(string $cacheDir, bool $debug)
    {
        $this->cacheDir = $cacheDir;
        $this->debug = $debug;

        parent::__construct();
    }

    public function configure()
    {
        $this->setDescription('Generate the classes.php files');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $classmap = $this->cacheDir.'/classes.map';

        if (!is_file($classmap)) {
            throw new \RuntimeException(sprintf('The file %s does not exist', $classmap));
        }

        $name = 'classes';
        $extension = '.php';

        $output->write('<info>Writing cache file ...</info>');
        ClassCollectionLoader::load(
            include($classmap),
            $this->cacheDir,
            $name,
            $this->debug,
            false,
            $extension
        );

        $output->writeln(' done!');

        return 0;
    }
}
