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

namespace SensioLabs\AdminBundle\Tests\User\Model;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Tests\App\Entity\Group;

final class GroupTest extends TestCase
{
    public function testDefaults(): void
    {
        $group = new Group();

        self::assertNull($group->getId());
        self::assertNull($group->getName());
        self::assertSame([], $group->getRoles());
    }

    public function testSettersAndGetters(): void
    {
        $group = new Group();

        $group->setName('Admins');
        self::assertSame('Admins', $group->getName());

        $group->setRoles(['ROLE_ADMIN', 'ROLE_EDITOR']);
        self::assertSame(['ROLE_ADMIN', 'ROLE_EDITOR'], $group->getRoles());
    }

    public function testHasRole(): void
    {
        $group = new Group();
        $group->setRoles(['ROLE_ADMIN']);

        self::assertTrue($group->hasRole('ROLE_ADMIN'));
        self::assertTrue($group->hasRole('role_admin'));
        self::assertFalse($group->hasRole('ROLE_EDITOR'));
    }

    public function testAddRole(): void
    {
        $group = new Group();

        $group->addRole('ROLE_ADMIN');
        self::assertTrue($group->hasRole('ROLE_ADMIN'));

        // Should not duplicate
        $group->addRole('ROLE_ADMIN');
        self::assertCount(1, $group->getRoles());

        // Should uppercase
        $group->addRole('role_editor');
        self::assertTrue($group->hasRole('ROLE_EDITOR'));
    }

    public function testRemoveRole(): void
    {
        $group = new Group();
        $group->setRoles(['ROLE_ADMIN', 'ROLE_EDITOR']);

        $group->removeRole('ROLE_ADMIN');
        self::assertFalse($group->hasRole('ROLE_ADMIN'));
        self::assertTrue($group->hasRole('ROLE_EDITOR'));
        self::assertSame(['ROLE_EDITOR'], $group->getRoles());
    }

    public function testToString(): void
    {
        $group = new Group();
        self::assertSame('', (string) $group);

        $group->setName('Admins');
        self::assertSame('Admins', (string) $group);
    }
}
