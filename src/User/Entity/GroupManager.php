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
use SensioLabs\AdminBundle\User\Model\GroupInterface;
use SensioLabs\AdminBundle\User\Model\GroupManagerInterface;

/**
 * @phpstan-implements GroupManagerInterface<GroupInterface>
 */
final class GroupManager implements GroupManagerInterface
{
    /**
     * @var EntityRepository<GroupInterface>
     */
    private EntityRepository $repository;

    /**
     * @param class-string<GroupInterface> $class
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $class,
    ) {
        /** @var EntityRepository<GroupInterface> $repository */
        $repository = $this->entityManager->getRepository($this->class);
        $this->repository = $repository;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function createGroup(): GroupInterface
    {
        $class = $this->class;

        return new $class();
    }

    public function findGroupBy(array $criteria): ?GroupInterface
    {
        return $this->repository->findOneBy($criteria);
    }

    public function updateGroup(GroupInterface $group): void
    {
        $this->entityManager->persist($group);
        $this->entityManager->flush();
    }

    public function deleteGroup(GroupInterface $group): void
    {
        $this->entityManager->remove($group);
        $this->entityManager->flush();
    }
}
