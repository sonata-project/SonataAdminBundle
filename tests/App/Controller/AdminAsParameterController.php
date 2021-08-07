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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sonata\AdminBundle\Tests\App\Admin\AdminAsParameterAdmin;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class AdminAsParameterController
{
    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @ParamConverter("admin", class="Sonata\AdminBundle\Tests\App\Admin\AdminAsParameterAdmin")
     */
    public function withAnnotation($admin): Response
    {
        if (!$admin instanceof AdminAsParameterAdmin) {
            throw new NotFoundHttpException();
        }

        return new Response();
    }

    public function test(AdminAsParameterAdmin $admin): Response
    {
        if ('test' !== $admin->getUniqid()) {
            throw new BadRequestHttpException();
        }

        return new Response();
    }
}
