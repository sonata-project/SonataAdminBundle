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

use Sonata\AdminBundle\Tests\App\Admin\AdminAsParameterAdmin;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class AdminAsParameterController
{
    public function test(AdminAsParameterAdmin $admin): Response
    {
        if ('test' !== $admin->getUniqId()) {
            throw new BadRequestHttpException();
        }

        return new Response();
    }
}
