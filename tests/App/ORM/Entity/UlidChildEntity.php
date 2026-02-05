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

namespace SensioLabs\AdminBundle\Tests\App\ORM\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
class UlidChildEntity implements \Stringable
{
    #[ORM\OneToOne(targetEntity: UuidEntity::class, mappedBy: 'child')]
    private ?UuidEntity $parent = null;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'ulid')]
        private Ulid $id,
        #[ORM\Column(type: Types::STRING)]
        private ?string $name = null,
    ) {
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getParent(): ?UuidEntity
    {
        return $this->parent;
    }

    public function setParent(?UuidEntity $parent): void
    {
        $this->parent = $parent;
    }
}
