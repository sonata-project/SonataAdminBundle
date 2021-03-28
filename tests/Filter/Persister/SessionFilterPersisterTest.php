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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Filter\Persister\SessionFilterPersister;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionFilterPersisterTest extends TestCase
{
    /**
     * @var SessionInterface&MockObject
     */
    private $session;

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
    }

    protected function tearDown(): void
    {
        unset($this->session);
    }

    public function testGetDefaultValueFromSessionIfNotDefined(): void
    {
        $this->session->expects($this->once())->method('get')
            ->with('admin.customer.filter.parameters', [])
            ->willReturn([]);

        self::assertSame([], $this->createPersister()->get('admin.customer'));
    }

    public function testGetValueFromSessionIfDefined(): void
    {
        $filters = [
            '_page' => 1,
            '_sort_by' => 'firstName',
            '_sort_order' => 'ASC',
            '_per_page' => 25,
        ];
        $this->session->expects($this->once())->method('get')
            ->with('admin.customer.filter.parameters', [])
            ->willReturn($filters);

        self::assertSame($filters, $this->createPersister()->get('admin.customer'));
    }

    public function testSetValueToSession(): void
    {
        $filters = [
            '_page' => 1,
            '_sort_by' => 'firstName',
            '_sort_order' => 'ASC',
            '_per_page' => 25,
        ];
        $this->session->expects($this->once())->method('set')
            ->with('admin.customer.filter.parameters', $filters)
            ->willReturn(null);

        $this->createPersister()->set('admin.customer', $filters);
    }

    public function testResetValueToSession(): void
    {
        $this->session->expects($this->once())->method('remove')
            ->with('admin.customer.filter.parameters')
            ->willReturn(null);

        $this->createPersister()->reset('admin.customer');
    }

    private function createPersister(): SessionFilterPersister
    {
        return new SessionFilterPersister($this->session);
    }
}
