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

final class CompositePrimaryKeysTest extends BasePantherTestCase
{
    public function testListCompositePrimaryKeys(): void
    {
        $this->client->request(Request::METHOD_GET, '/admin/tests/app/car/list');

        self::assertSelectorTextContains('.box-footer', '1 / 1  -  3 results');
    }

    public function testListRelationsCompositePrimaryKeys(): void
    {
        $this->client->request(Request::METHOD_GET, '/admin/tests/app/item/list');

        self::assertSelectorTextContains('.box-footer', '1 / 1  -  3 results');
    }
}
