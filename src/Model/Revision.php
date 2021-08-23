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
    /**
     * @var int|string
     */
    private $id;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var string
     */
    private $username;

    /**
     * @param int|string $id
     */
    public function __construct($id, \DateTimeInterface $dateTime, string $username)
    {
        $this->id = $id;
        $this->dateTime = $dateTime;
        $this->username = $username;
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int|string
     */
    public function getRev()
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

    public function getUsername(): string
    {
        return $this->username;
    }
}
