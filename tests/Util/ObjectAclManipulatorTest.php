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

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Util\DummyObjectAclManipulator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;

/**
 * @author Grégoire Paris <postmaster@greg0ire.fr>
 */
class ObjectAclManipulatorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->output = $this->prophesize(OutputInterface::class);
        $this->admin = $this->prophesize(AdminInterface::class);
        $this->oids = new \ArrayIterator([
            $this->prophesize(ObjectIdentityInterface::class)->reveal(),
            $this->prophesize(ObjectIdentityInterface::class)->reveal(),
        ]);
        $this->securityIdentity = new UserSecurityIdentity('Michael', \stdClass::class);
    }

    public function testConfigureAclsIgnoresNonAclSecurityHandlers(): void
    {
        $this->admin->getSecurityHandler()->shouldBeCalled();
        $this->admin->getCode()->shouldBeCalled()->willReturn('test');
        $this->output->writeln(Argument::allOf(
            Argument::containingString('ignoring'),
            Argument::containingString('test')
        ))->shouldBeCalled();
        $manipulator = new DummyObjectAclManipulator();
        $this->assertSame(
            [0, 0],
            $manipulator->configureAcls(
                $this->output->reveal(),
                $this->admin->reveal(),
                $this->oids,
                $this->securityIdentity
            )
        );
    }

    public function testConfigureAcls(): void
    {
        $securityHandler = $this->prophesize(AclSecurityHandlerInterface::class);
        $acls = $this->prophesize('SplObjectStorage');
        $acls->contains(Argument::type(ObjectIdentityInterface::class))
            ->shouldBeCalled()
            ->willReturn(false, true);
        $acl = $this->prophesize(AclInterface::class)->reveal();
        $acls->offsetGet(Argument::type(ObjectIdentityInterface::class))
            ->shouldBeCalled()
            ->willReturn($acl);
        $securityHandler->findObjectAcls($this->oids)->shouldBeCalled()->willReturn($acls->reveal());
        $securityHandler->createAcl(Argument::type(ObjectIdentityInterface::class))->shouldBeCalled()->willReturn($acl);
        $securityHandler->addObjectOwner($acl, Argument::type(UserSecurityIdentity::class))->shouldBeCalled();
        $securityHandler->buildSecurityInformation($this->admin)->shouldBeCalled()->willReturn([]);
        $securityHandler->addObjectClassAces($acl, [])->shouldBeCalled();
        $securityHandler->updateAcl($acl)->shouldBeCalled()->willThrow(new \Exception('test exception'));
        $this->output->writeln(Argument::allOf(
            Argument::containingString('ignoring'),
            Argument::containingString('test exception')
        ))->shouldBeCalled();

        $this->admin->getSecurityHandler()->shouldBeCalled()->willReturn($securityHandler->reveal());

        $manipulator = new DummyObjectAclManipulator();

        $this->assertSame(
            [1, 1],
            $manipulator->configureAcls(
                $this->output->reveal(),
                $this->admin->reveal(),
                $this->oids,
                $this->securityIdentity
            )
        );
    }
}
