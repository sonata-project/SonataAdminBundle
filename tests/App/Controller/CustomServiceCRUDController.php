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

use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Controller\CRUDControllerInterface;
use Sonata\AdminBundle\Tests\App\Admin\FooAdminWithCustomServiceControllerAdmin;
use Symfony\Component\HttpFoundation\Response;

final class CustomServiceCRUDController extends CRUDController implements CRUDControllerInterface
{
    public function customAction(): Response
    {
        return new Response();
    }

    public static function getSupportedAdmin(): string
    {
        return FooAdminWithCustomServiceControllerAdmin::class;
    }
}
