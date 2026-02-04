<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\App\Controller;

use SensioLabs\AdminBundle\Tests\App\Admin\AdminAsParameterAdmin;
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
