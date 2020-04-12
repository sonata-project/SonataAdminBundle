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

use Sonata\AdminBundle\Tests\Functional\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PR6031Test extends WebTestCase
{
    public function testLabelInShowAction(): void
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, '/admin/tests/app/pr6031/pr_6031/edit');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $client->request(Request::METHOD_GET, '/admin/tests/app/pr6031/pr_6031/show');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $client->request(Request::METHOD_GET, '/admin/tests/app/pr6031/create');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $client->request(Request::METHOD_GET, '/admin/tests/app/pr6031/list');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
}
