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

namespace SensioLabs\AdminBundle\Tests\App\Action;

use Symfony\Component\HttpFoundation\Response;

final class BrowseAction
{
    public function __invoke(): Response
    {
        return new Response();
    }
}
