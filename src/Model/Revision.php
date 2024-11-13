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

namespace Sonata\AdminBundle\Model;

final class Revision
{
    public function __construct(
        private int|string $id,
        private \DateTimeInterface $dateTime,
        private ?string $username,
    ) {
    }

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getRev(): int|string
    {
        return $this->id;
    }

    public function getDateTime(): \DateTimeInterface
    {
        return $this->dateTime;
    }

    public function getTimestamp(): \DateTimeInterface
    {
        return $this->dateTime;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }
}
