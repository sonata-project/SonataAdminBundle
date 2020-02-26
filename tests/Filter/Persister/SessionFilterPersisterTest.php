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

namespace Sonata\AdminBundle\Tests\Filter\Persister;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Sonata\AdminBundle\Filter\Persister\SessionFilterPersister;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionFilterPersisterTest extends TestCase
{
    /**
     * @var SessionInterface|ObjectProphecy
     */
    private $session;

    public function setUp(): void
    {
        $this->session = $this->prophesize(SessionInterface::class);
    }

    public function tearDown(): void
    {
        unset($this->session);
    }

    public function testGetDefaultValueFromSessionIfNotDefined(): void
    {
        $this->session->get('admin.customer.filter.parameters', [])
            ->shouldBeCalledTimes(1)
            ->willReturn([]);

        self::assertSame([], $this->createPersister()->get('admin.customer'));
    }

    public function testGetValueFromSessionIfDefined(): void
    {
        $filters = [
            'active' => true,
            '_page' => 1,
            '_sort_by' => 'firstName',
            '_sort_order' => 'ASC',
            '_per_page' => 25,
        ];
        $this->session->get('admin.customer.filter.parameters', [])
            ->shouldBeCalledTimes(1)
            ->willReturn($filters);

        self::assertSame($filters, $this->createPersister()->get('admin.customer'));
    }

    public function testSetValueToSession(): void
    {
        $filters = [
            'active' => true,
            '_page' => 1,
            '_sort_by' => 'firstName',
            '_sort_order' => 'ASC',
            '_per_page' => 25,
        ];
        $this->session->set('admin.customer.filter.parameters', $filters)
            ->shouldBeCalledTimes(1)
            ->willReturn(null);

        $this->createPersister()->set('admin.customer', $filters);
    }

    public function testResetValueToSession(): void
    {
        $this->session->remove('admin.customer.filter.parameters')
            ->shouldBeCalledTimes(1)
            ->willReturn(null);

        $this->createPersister()->reset('admin.customer');
    }

    private function createPersister(): SessionFilterPersister
    {
        return new SessionFilterPersister($this->session->reveal());
    }
}
