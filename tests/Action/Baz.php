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

namespace SensioLabs\AdminBundle\Tests\Action;

final class Baz
{
    private ?Bar $bar = null;

    public function setBar(Bar $bar): void
    {
        $this->bar = $bar;
    }

    public function getBar(): ?Bar
    {
        return $this->bar;
    }
}
