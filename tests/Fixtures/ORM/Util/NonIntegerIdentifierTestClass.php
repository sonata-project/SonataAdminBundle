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

namespace Sonata\DoctrineORMAdminBundle\Tests\Fixtures\Util;

/**
 * This class is used in the ModelManagerTest suite to test non integer/string identifiers.
 *
 * @author Jeroen Thora <jeroen.thora@gmail.com>
 */
final class NonIntegerIdentifierTestClass implements \Stringable
{
    public function __construct(private string $uuid)
    {
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->uuid;
    }
}
