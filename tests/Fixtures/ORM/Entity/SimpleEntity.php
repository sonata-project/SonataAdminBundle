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

final class SimpleEntity
{
    private ?int $schmeckles = null;

    private ?string $multiWordProperty = null;

    public function getSchmeckles(): ?int
    {
        return $this->schmeckles;
    }

    public function setSchmeckles(?int $value): void
    {
        $this->schmeckles = $value;
    }

    public function getMultiWordProperty(): ?string
    {
        return $this->multiWordProperty;
    }

    public function setMultiWordProperty(?string $value): void
    {
        $this->multiWordProperty = $value;
    }
}
