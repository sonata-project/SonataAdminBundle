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

namespace SensioLabs\AdminBundle\Tests\Security\Handler;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Security\Handler\NoopSecurityHandler;

final class NoopSecurityHandlerTest extends TestCase
{
    private NoopSecurityHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new NoopSecurityHandler();
    }

    public function testIsGranted(): void
    {
        static::assertTrue($this->handler->isGranted($this->getSonataAdminObject(), 'TOTO'));
    }

    public function testBuildSecurityInformation(): void
    {
        static::assertSame([], $this->handler->buildSecurityInformation($this->getSonataAdminObject()));
    }

    #[DoesNotPerformAssertions]
    public function testCreateObjectSecurity(): void
    {
        $this->handler->createObjectSecurity($this->getSonataAdminObject(), new \stdClass());
    }

    #[DoesNotPerformAssertions]
    public function testDeleteObjectSecurity(): void
    {
        $this->handler->deleteObjectSecurity($this->getSonataAdminObject(), new \stdClass());
    }

    public function testGetBaseRole(): void
    {
        static::assertSame('', $this->handler->getBaseRole($this->getSonataAdminObject()));
    }

    /**
     * @return AdminInterface<object>&MockObject
     */
    private function getSonataAdminObject(): AdminInterface
    {
        return $this->createMock(AdminInterface::class);
    }
}
