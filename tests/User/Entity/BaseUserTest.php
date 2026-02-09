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

namespace SensioLabs\AdminBundle\Tests\User\Entity;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Tests\App\Entity\User;

final class BaseUserTest extends TestCase
{
    public function testPrePersistSetsTimestamps(): void
    {
        $user = new User();
        self::assertNull($user->getCreatedAt());
        self::assertNull($user->getUpdatedAt());

        $user->prePersist();

        self::assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $user->getUpdatedAt());
    }

    public function testPreUpdateSetsUpdatedAt(): void
    {
        $user = new User();
        $user->prePersist();

        $originalCreatedAt = $user->getCreatedAt();

        // Small sleep to ensure different timestamps
        $user->preUpdate();

        self::assertSame($originalCreatedAt, $user->getCreatedAt());
        self::assertInstanceOf(\DateTimeImmutable::class, $user->getUpdatedAt());
    }
}
