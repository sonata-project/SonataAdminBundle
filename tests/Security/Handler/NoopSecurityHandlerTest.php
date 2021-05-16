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

    protected function setUp(): void
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

    /**
     * @doesNotPerformAssertions
     */
    public function testCreateObjectSecurity(): void
    {
        $this->handler->createObjectSecurity($this->getSonataAdminObject(), new \stdClass());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testDeleteObjectSecurity(): void
    {
        $this->handler->deleteObjectSecurity($this->getSonataAdminObject(), new \stdClass());
    }

    public function testGetBaseRole(): void
    {
        $this->assertSame('', $this->handler->getBaseRole($this->getSonataAdminObject()));
    }

    /**
     * @return AdminInterface<object>
     */
    private function getSonataAdminObject(): AdminInterface
    {
        return $this->getMockForAbstractClass(AdminInterface::class);
    }
}
