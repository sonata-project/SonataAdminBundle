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

namespace Sonata\AdminBundle\Tests\Security\Handler;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\NoopSecurityHandler;

class NoopSecurityHandlerTest extends TestCase
{
    /**
     * @var NoopSecurityHandler
     */
    private $handler;

    public function setUp(): void
    {
        $this->handler = new NoopSecurityHandler();
    }

    public function testIsGranted(): void
    {
        $this->assertTrue($this->handler->isGranted($this->getSonataAdminObject(), ['TOTO']));
        $this->assertTrue($this->handler->isGranted($this->getSonataAdminObject(), 'TOTO'));
    }

    public function testBuildSecurityInformation(): void
    {
        $this->assertSame([], $this->handler->buildSecurityInformation($this->getSonataAdminObject()));
    }

    public function testCreateObjectSecurity(): void
    {
        $this->assertNull($this->handler->createObjectSecurity($this->getSonataAdminObject(), new \stdClass()));
    }

    public function testDeleteObjectSecurity(): void
    {
        $this->assertNull($this->handler->deleteObjectSecurity($this->getSonataAdminObject(), new \stdClass()));
    }

    public function testGetBaseRole(): void
    {
        $this->assertSame('', $this->handler->getBaseRole($this->getSonataAdminObject()));
    }

    private function getSonataAdminObject(): AdminInterface
    {
        return $this->getMockForAbstractClass(AdminInterface::class);
    }
}
