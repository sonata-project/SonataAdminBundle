<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Util;

use Prophecy\Argument;
use Sonata\AdminBundle\Tests\Fixtures\Util\DummyObjectAclManipulator;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

/**
 * @author Grégoire Paris <postmaster@greg0ire.fr>
 */
class ObjectAclManipulatorTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->output = $this->prophesize('Symfony\Component\Console\Output\OutputInterface');
        $this->admin = $this->prophesize('Sonata\AdminBundle\Admin\AdminInterface');
        $this->oids = new \ArrayIterator([
            $this->prophesize('Symfony\Component\Security\Acl\Model\ObjectIdentityInterface')->reveal(),
            $this->prophesize('Symfony\Component\Security\Acl\Model\ObjectIdentityInterface')->reveal(),
        ]);
        $this->securityIdentity = new UserSecurityIdentity('Michael', 'stdClass');
    }

    public function testConfigureAclsIgnoresNonAclSecurityHandlers()
    {
        $this->admin->getSecurityHandler()->shouldBeCalled();
        $this->admin->getCode()->shouldBeCalled()->willReturn('test');
        $this->output->writeln(Argument::allof(
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

    public function testConfigureAcls()
    {
        $securityHandler = $this->prophesize('Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface');
        $acls = $this->prophesize('SplObjectStorage');
        $acls->contains(Argument::type('Symfony\Component\Security\Acl\Model\ObjectIdentityInterface'))
            ->shouldBeCalled()
            ->willReturn(false, true);
        $acl = $this->prophesize('Symfony\Component\Security\Acl\Model\AclInterface')->reveal();
        $acls->offsetGet(Argument::Type('Symfony\Component\Security\Acl\Model\ObjectIdentityInterface'))
            ->shouldBeCalled()
            ->willReturn($acl);
        $securityHandler->findObjectAcls($this->oids)->shouldBeCalled()->willReturn($acls->reveal());
        $securityHandler->createAcl(Argument::type(
            'Symfony\Component\Security\Acl\Model\ObjectIdentityInterface'
        ))->shouldBeCalled()->willReturn($acl);
        $securityHandler->addObjectOwner($acl, Argument::type(
            'Symfony\Component\Security\Acl\Domain\UserSecurityIdentity'
        ))->shouldBeCalled();
        $securityHandler->buildSecurityInformation($this->admin)->shouldBeCalled()->willReturn([]);
        $securityHandler->addObjectClassAces($acl, [])->shouldBeCalled();
        $securityHandler->updateAcl($acl)->shouldBeCalled()->willThrow(new \Exception('test exception'));
        $this->output->writeln(Argument::allof(
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
