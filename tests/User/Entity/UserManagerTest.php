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
use SensioLabs\AdminBundle\Tests\App\Entity\User;
use SensioLabs\AdminBundle\User\Entity\UserManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserManagerTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private UserPasswordHasherInterface&MockObject $passwordHasher;
    private UserManager $userManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $repository = $this->createMock(EntityRepository::class);
        $this->entityManager->method('getRepository')->willReturn($repository);

        $this->userManager = new UserManager(
            $this->entityManager,
            $this->passwordHasher,
            User::class,
        );
    }

    public function testGetClass(): void
    {
        self::assertSame(User::class, $this->userManager->getClass());
    }

    public function testCreateUser(): void
    {
        $user = $this->userManager->createUser();

        self::assertInstanceOf(User::class, $user);
    }

    public function testUpdatePasswordHashesPlainPassword(): void
    {
        $user = new User();
        $user->setPlainPassword('secret');

        $this->passwordHasher->expects(self::once())
            ->method('hashPassword')
            ->with($user, 'secret')
            ->willReturn('hashed_secret');

        $this->userManager->updatePassword($user);

        self::assertSame('hashed_secret', $user->getPassword());
        self::assertNull($user->getPlainPassword());
    }

    public function testUpdatePasswordSkipsEmptyPlainPassword(): void
    {
        $user = new User();
        $user->setPassword('existing_hash');

        $this->passwordHasher->expects(self::never())
            ->method('hashPassword');

        $this->userManager->updatePassword($user);

        self::assertSame('existing_hash', $user->getPassword());
    }

    public function testDeleteUser(): void
    {
        $user = new User();

        $this->entityManager->expects(self::once())
            ->method('remove')
            ->with($user);

        $this->entityManager->expects(self::once())
            ->method('flush');

        $this->userManager->deleteUser($user);
    }
}
