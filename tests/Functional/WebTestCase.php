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

namespace Sonata\AdminBundle\Tests\Functional;

use Sonata\AdminBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }
}
