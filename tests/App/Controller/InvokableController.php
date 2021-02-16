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

namespace Sonata\AdminBundle\Tests\App\Controller;

use Sonata\AdminBundle\Tests\App\Admin\TestingParamConverterAdmin;
use Symfony\Component\HttpFoundation\Response;

final class InvokableController
{
    public function __invoke(TestingParamConverterAdmin $admin): Response
    {
        return new Response();
    }
}
