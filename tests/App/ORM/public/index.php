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
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../../../vendor/autoload.php';

$kernel = new AppKernel('test', false);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
