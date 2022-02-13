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

namespace Sonata\AdminBundle\Tests\App\Action;

use Symfony\Component\HttpFoundation\Response;

class BrowseAction
{
    public function __invoke(): Response
    {
        return new Response();
    }
}
