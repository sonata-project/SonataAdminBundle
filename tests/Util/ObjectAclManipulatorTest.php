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
final class ObjectAclManipulatorTest extends TestCase
{
    /**
     * @var MockObject&OutputInterface
     */
    private OutputInterface $output;

    /**
     * @var MockObject&AdminInterface<object>
     */
    private AdminInterface $admin;

    /**
     * @var \ArrayIterator<int, MockObject&ObjectIdentityInterface>
     */
    private \ArrayIterator $oids;

    private UserSecurityIdentity $securityIdentity;

    protected function setUp(): void
    {
        $this->output = $this->createMock(OutputInterface::class);
        $this->admin = $this->createMock(AdminInterface::class);
        /** @psalm-suppress InvalidPropertyAssignmentValue https://github.com/vimeo/psalm/issues/9501 */
        $this->oids = new \ArrayIterator([
            $this->createMock(ObjectIdentityInterface::class),
            $this->createMock(ObjectIdentityInterface::class),
        ]);
        $this->securityIdentity = new UserSecurityIdentity('Michael', \stdClass::class);
    }

    public function testConfigureAclsIgnoresNonAclSecurityHandlers(): void
    {
        $this->admin->expects(static::once())->method('getSecurityHandler');
        $this->admin->expects(static::once())->method('getCode')->willReturn('test');
        $this->output->expects(static::once())->method('writeln')->with(static::logicalAnd(
            static::stringContains('ignoring'),
            static::stringContains('test')
        ));
        $manipulator = new DummyObjectAclManipulator();
        static::assertSame(
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
        $acls->expects(static::atLeastOnce())->method('contains')->with(static::isInstanceOf(ObjectIdentityInterface::class))
            ->willReturn(false, true);
        $acl = $this->createStub(MutableAclInterface::class);
        $acls->expects(static::once())->method('offsetGet')->with(static::isInstanceOf(ObjectIdentityInterface::class))
            ->willReturn($acl);
        $securityHandler->expects(static::once())->method('findObjectAcls')->with($this->oids)->willReturn($acls);
        $securityHandler->expects(static::once())->method('createAcl')->with(static::isInstanceOf(ObjectIdentityInterface::class))->willReturn($acl);
        $securityHandler->expects(static::atLeastOnce())->method('addObjectOwner')->with($acl, static::isInstanceOf(UserSecurityIdentity::class));
        $securityHandler->expects(static::atLeastOnce())->method('addObjectClassAces')->with($acl, $this->admin);
        $securityHandler->expects(static::atLeastOnce())->method('updateAcl')->with($acl)->willThrowException(new \Exception('test exception'));
        $this->output->method('writeln')->with(static::logicalAnd(
            static::stringContains('ignoring'),
            static::stringContains('test exception')
        ));

        $this->admin->expects(static::once())->method('getSecurityHandler')->willReturn($securityHandler);

        $manipulator = new DummyObjectAclManipulator();

        static::assertSame(
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
