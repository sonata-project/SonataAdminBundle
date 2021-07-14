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
use Symfony\Bundle\FrameworkBundle\Console\Application;

require __DIR__.'/vendor/autoload.php';

$kernel = new AppKernel();

return new Application($kernel);
