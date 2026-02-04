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

namespace SensioLabs\AdminBundle\Request;

use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Exception\AdminCodeNotFoundException;
use Symfony\Component\HttpFoundation\Request;

interface AdminFetcherInterface
{
    /**
     * @throws \InvalidArgumentException  if the admin code is not found in the request
     * @throws AdminCodeNotFoundException if no admin was found for the admin code provided
     *
     * @return AdminInterface<object>
     */
    public function get(Request $request): AdminInterface;
}
