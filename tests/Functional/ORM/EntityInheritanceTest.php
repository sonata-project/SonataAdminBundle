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

namespace SensioLabs\AdminBundle\Tests\Functional\ORM;

use Symfony\Component\HttpFoundation\Request;

final class EntityInheritanceTest extends BaseFunctionalTestCase
{
    public function testList(): void
    {
        $this->client->request(Request::METHOD_GET, '/admin/tests/app/sub/list');

        self::assertResponseIsSuccessful();
    }
}
