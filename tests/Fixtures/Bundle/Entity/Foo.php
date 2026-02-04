<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Fixtures\Bundle\Entity;

final class Foo
{
    private ?string $bar = null;

    private ?string $baz = null;

    public function getBar(): ?string
    {
        return $this->bar;
    }

    public function setBar(string $bar): void
    {
        $this->bar = $bar;
    }

    public function getBaz(): ?string
    {
        return $this->baz;
    }

    public function setBaz(string $baz): void
    {
        $this->baz = $baz;
    }
}
