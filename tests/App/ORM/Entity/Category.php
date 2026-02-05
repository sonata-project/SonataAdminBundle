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

#[ORM\Entity]
class Category implements \Stringable
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: Types::STRING)]
        #[ORM\GeneratedValue(strategy: 'NONE')]
        private string $id = '',
        #[ORM\Column(type: Types::STRING)]
        private string $name = '',
    ) {
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
