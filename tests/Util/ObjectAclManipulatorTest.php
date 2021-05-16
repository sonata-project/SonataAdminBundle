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

namespace Sonata\AdminBundle\Tests\Util;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Util\DummyObjectAclManipulator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;

/**
 * @author Grégoire Paris <postmaster@greg0ire.fr>
 */
class ObjectAclManipulatorTest extends TestCase
{
    /**
     * @var MockObject&OutputInterface
     */
    private $output;

    /**
     * @var MockObject&AdminInterface<object>
     */
    private $admin;

    /**
     * @var \ArrayIterator<int, MockObject&ObjectIdentityInterface>
     */
    private $oids;

    /**
     * @var UserSecurityIdentity
     */
    private $securityIdentity;

    protected function setUp(): void
    {
        $this->output = $this->createMock(OutputInterface::class);
        $this->admin = $this->createMock(AdminInterface::class);
        $this->oids = new \ArrayIterator([
            $this->createMock(ObjectIdentityInterface::class),
            $this->createMock(ObjectIdentityInterface::class),
        ]);
        $this->securityIdentity = new UserSecurityIdentity('Michael', \stdClass::class);
    }

    public function testConfigureAclsIgnoresNonAclSecurityHandlers(): void
    {
        $this->admin->expects($this->once())->method('getSecurityHandler');
        $this->admin->expects($this->once())->method('getCode')->willReturn('test');
        $this->output->expects($this->once())->method('writeln')->with($this->logicalAnd(
            $this->stringContains('ignoring'),
            $this->stringContains('test')
        ));
        $manipulator = new DummyObjectAclManipulator();
        $this->assertSame(
            [0, 0],
            $manipulator->configureAcls(
                $this->output,
                $this->admin,
                $this->oids,
                $this->securityIdentity
            )
        );
    }

    public function testConfigureAcls(): void
    {
        $securityHandler = $this->createMock(AclSecurityHandlerInterface::class);
        $acls = $this->createMock(\SplObjectStorage::class);
        $acls->expects($this->atLeastOnce())->method('contains')->with($this->isInstanceOf(ObjectIdentityInterface::class))
            ->willReturn(false, true);
        $acl = $this->createStub(MutableAclInterface::class);
        $acls->expects($this->once())->method('offsetGet')->with($this->isInstanceOf(ObjectIdentityInterface::class))
            ->willReturn($acl);
        $securityHandler->expects($this->once())->method('findObjectAcls')->with($this->oids)->willReturn($acls);
        $securityHandler->expects($this->once())->method('createAcl')->with($this->isInstanceOf(ObjectIdentityInterface::class))->willReturn($acl);
        $securityHandler->expects($this->atLeastOnce())->method('addObjectOwner')->with($acl, $this->isInstanceOf(UserSecurityIdentity::class));
        $securityHandler->expects($this->atLeastOnce())->method('buildSecurityInformation')->with($this->admin)->willReturn([]);
        $securityHandler->expects($this->atLeastOnce())->method('addObjectClassAces')->with($acl, []);
        $securityHandler->expects($this->atLeastOnce())->method('updateAcl')->with($acl)->willThrowException(new \Exception('test exception'));
        $this->output->method('writeln')->with($this->logicalAnd(
            $this->stringContains('ignoring'),
            $this->stringContains('test exception')
        ));

        $this->admin->expects($this->once())->method('getSecurityHandler')->willReturn($securityHandler);

        $manipulator = new DummyObjectAclManipulator();

        $this->assertSame(
            [1, 1],
            $manipulator->configureAcls(
                $this->output,
                $this->admin,
                $this->oids,
                $this->securityIdentity
            )
        );
    }
}
