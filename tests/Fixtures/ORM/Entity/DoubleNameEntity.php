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

namespace Sonata\DoctrineORMAdminBundle\Tests\Fixtures\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class DoubleNameEntity
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: Types::INTEGER)]
        public int $id,
        #[ORM\Column(type: Types::STRING)]
        public string $name,
        #[ORM\Column(type: Types::STRING, nullable: true)]
        public ?string $name2 = null,
    ) {
    }
}
