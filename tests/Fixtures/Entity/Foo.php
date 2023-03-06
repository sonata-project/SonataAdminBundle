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

namespace Sonata\AdminBundle\Tests\Fixtures\Entity;

final class Foo implements \Stringable
{
    private string|int|null $bar = null;

    private string|int|null $baz = null;

    public function __toString(): string
    {
        return (string) $this->bar;
    }

    public function getBar(): int|string|null
    {
        return $this->bar;
    }

    public function setBar(string|int $bar): void
    {
        $this->bar = $bar;
    }

    public function getBaz(): int|string|null
    {
        return $this->baz;
    }

    public function setBaz(string|int $baz): void
    {
        $this->baz = $baz;
    }
}
