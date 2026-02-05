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

namespace Sonata\DoctrineORMAdminBundle\Tests\Fixtures\DoctrineType;

final class ValueObjectWithToStringImpl
{
    public function __construct(private string $uuid)
    {
    }

    public function getValue(): string
    {
        return $this->uuid;
    }

    public function toString(): string
    {
        return $this->uuid;
    }
}
