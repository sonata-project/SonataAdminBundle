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

final class DatagridTest extends BasePantherTestCase
{
    public function testFilter(): void
    {
        $this->client->request(Request::METHOD_GET, '/admin/tests/app/category/list');

        $this->client->clickLink('Filters');
        $this->client->clickLink('Name');

        $this->client->submitForm('Filter', [
            'filter[name][value]' => 'Dystopian',
        ]);

        self::assertSelectorTextContains('.sonata-link-identifier', 'Dystopian');
    }
}
