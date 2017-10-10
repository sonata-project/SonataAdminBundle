<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Route\Handler;

use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\HttpFoundation\Request;

interface RouteIdHandlerInterface
{
    /**
     * @param Request        $request
     * @param AdminInterface $admin
     *
     * @return int|string
     */
    public function getIdFromRequest(Request $request, AdminInterface $admin);
}
