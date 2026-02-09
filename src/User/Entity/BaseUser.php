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

use Doctrine\ORM\Mapping as ORM;
use SensioLabs\AdminBundle\User\Model\User;

#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class BaseUser extends User
{
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    protected ?string $email = null;

    #[ORM\Column(type: 'string')]
    protected ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    protected bool $enabled = false;

    #[ORM\Column(type: 'json')]
    protected array $roles = [];

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    protected ?string $firstname = null;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    protected ?string $lastname = null;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    protected ?string $phone = null;

    #[ORM\Column(type: 'string', length: 8, nullable: true)]
    protected ?string $locale = null;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    protected ?string $timezone = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: true)]
    protected ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
    protected ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(name: 'confirmation_token', type: 'string', length: 180, unique: true, nullable: true)]
    protected ?string $confirmationToken = null;

    #[ORM\Column(name: 'password_requested_at', type: 'datetime_immutable', nullable: true)]
    protected ?\DateTimeImmutable $passwordRequestedAt = null;

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
