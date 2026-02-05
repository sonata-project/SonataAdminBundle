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
class ProductAttribute
{
    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: Product::class)]
        private Product $product,
        #[ORM\Column(type: Types::STRING)]
        private string $name,
    ) {
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
