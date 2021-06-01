<?php

use Sonata\AdminBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;

require __DIR__ . '/vendor/autoload.php';

$kernel = new AppKernel();

return new Application($kernel);
