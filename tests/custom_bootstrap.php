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

use Sonata\AdminBundle\Tests\App\AppKernel;

$_ENV['KERNEL_CLASS'] = AppKernel::class;
$_ENV['APP_ENV'] = 'test';
$_ENV['APP_DEBUG'] = true;

putenv(sprintf('KERNEL_CLASS=%s', $_ENV['KERNEL_CLASS']));
putenv(sprintf('APP_ENV=%s', $_ENV['APP_ENV']));
putenv(sprintf('APP_DEBUG=%s', $_ENV['APP_DEBUG']));
