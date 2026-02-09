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

namespace SensioLabs\AdminBundle\Tests\User\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Tests\App\Entity\Group;
use SensioLabs\AdminBundle\User\Entity\GroupManager;

final class GroupManagerTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private GroupManager $groupManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $repository = $this->createMock(EntityRepository::class);
        $this->entityManager->method('getRepository')->willReturn($repository);

        $this->groupManager = new GroupManager(
            $this->entityManager,
            Group::class,
        );
    }

    public function testGetClass(): void
    {
        self::assertSame(Group::class, $this->groupManager->getClass());
    }

    public function testCreateGroup(): void
    {
        $group = $this->groupManager->createGroup();

        self::assertInstanceOf(Group::class, $group);
    }

    public function testDeleteGroup(): void
    {
        $group = new Group();

        $this->entityManager->expects(self::once())
            ->method('remove')
            ->with($group);

        $this->entityManager->expects(self::once())
            ->method('flush');

        $this->groupManager->deleteGroup($group);
    }

    public function testUpdateGroup(): void
    {
        $group = new Group();

        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with($group);

        $this->entityManager->expects(self::once())
            ->method('flush');

        $this->groupManager->updateGroup($group);
    }
}
