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

namespace SensioLabs\AdminBundle\User\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SensioLabs\AdminBundle\User\Model\UserInterface;
use SensioLabs\AdminBundle\User\Model\UserManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @phpstan-implements UserManagerInterface<UserInterface>
 */
final class UserManager implements UserManagerInterface
{
    /**
     * @var EntityRepository<UserInterface>
     */
    private EntityRepository $repository;

    /**
     * @param class-string<UserInterface> $class
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly string $class,
    ) {
        /** @var EntityRepository<UserInterface> $repository */
        $repository = $this->entityManager->getRepository($this->class);
        $this->repository = $repository;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function createUser(): UserInterface
    {
        $class = $this->class;

        return new $class();
    }

    public function findUserByEmail(string $email): ?UserInterface
    {
        return $this->repository->findOneBy(['email' => $email]);
    }

    public function findUserByConfirmationToken(string $token): ?UserInterface
    {
        return $this->repository->findOneBy(['confirmationToken' => $token]);
    }

    public function updateUser(UserInterface $user): void
    {
        $this->updatePassword($user);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function deleteUser(UserInterface $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function updatePassword(UserInterface $user): void
    {
        $plainPassword = $user->getPlainPassword();

        if (null === $plainPassword || '' === $plainPassword) {
            return;
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $user->eraseCredentials();
    }
}
