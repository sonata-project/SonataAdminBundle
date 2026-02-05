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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Mother implements \Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    /**
     * @var Collection<array-key, Child>
     */
    #[ORM\ManyToMany(targetEntity: Child::class, cascade: ['persist', 'remove'])]
    private Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function addChild(Child $child): void
    {
        $this->children->add($child);
    }

    public function removeChild(Child $child): void
    {
        $this->children->removeElement($child);
    }

    /**
     * @return Collection<array-key, Child>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }
}
