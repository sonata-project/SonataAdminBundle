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
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class UuidEntity implements \Stringable
{
    #[ORM\Column(type: Types::STRING)]
    private ?string $name = null;

    #[ORM\OneToOne(targetEntity: UlidChildEntity::class, inversedBy: 'parent', cascade: ['persist'])]
    #[ORM\JoinColumn(referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?UlidChildEntity $child = null;

    #[ORM\ManyToOne(targetEntity: Car::class)]
    #[ORM\JoinColumn(name: 'car_name', referencedColumnName: 'name', options: ['length' => 255])]
    #[ORM\JoinColumn(name: 'car_year', referencedColumnName: 'year')]
    private ?Car $car = null;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'uuid')]
        private Uuid $id,
    ) {
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getId(): Uuid
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

    public function getChild(): ?UlidChildEntity
    {
        return $this->child;
    }

    public function setChild(?UlidChildEntity $child): void
    {
        $this->child = $child;
    }

    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(?Car $car): void
    {
        $this->car = $car;
    }
}
