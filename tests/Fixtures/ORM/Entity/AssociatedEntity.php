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

namespace SensioLabs\AdminBundle\Tests\Fixtures\ORM\Entity;

use SensioLabs\AdminBundle\Tests\Fixtures\ORM\Entity\Embeddable\EmbeddedEntity;

final class AssociatedEntity
{
    public function __construct(
        public EmbeddedEntity $embeddedEntity,
        private int $plainField,
    ) {
    }

    public function getPlainField(): int
    {
        return $this->plainField;
    }
}
