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

use SensioLabs\AdminBundle\Tests\App\ORM\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;

$kernel = new AppKernel($_SERVER['APP_ENV'] ?? 'test', (bool) ($_SERVER['APP_DEBUG'] ?? false));

(new Filesystem())->remove([$kernel->getCacheDir()]);

$application = new Application($kernel);
$application->setCatchExceptions(false);
$application->setAutoExit(false);

$input = new ArrayInput([
    'command' => 'doctrine:database:drop',
    '--force' => true,
]);
$application->run($input, new NullOutput());

$input = new ArrayInput([
    'command' => 'doctrine:database:create',
    '--no-interaction' => true,
]);
$application->run($input, new NullOutput());

$input = new ArrayInput([
    'command' => 'doctrine:schema:create',
]);
$application->run($input, new NullOutput());

$input = new ArrayInput([
    'command' => 'doctrine:fixtures:load',
    '--no-interaction' => false,
]);
$application->run($input, new NullOutput());

$input = new ArrayInput([
    'command' => 'assets:install',
    'target' => __DIR__.'/App/public',
    '--symlink' => true,
]);
$application->run($input, new NullOutput());

restore_error_handler();
