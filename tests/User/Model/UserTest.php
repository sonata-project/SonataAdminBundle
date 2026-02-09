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
use SensioLabs\AdminBundle\Tests\App\Entity\User;
use SensioLabs\AdminBundle\User\Model\UserInterface;

final class UserTest extends TestCase
{
    public function testDefaults(): void
    {
        $user = new User();

        self::assertNull($user->getId());
        self::assertNull($user->getEmail());
        self::assertNull($user->getPassword());
        self::assertNull($user->getPlainPassword());
        self::assertFalse($user->isEnabled());
        self::assertNull($user->getFirstname());
        self::assertNull($user->getLastname());
        self::assertNull($user->getPhone());
        self::assertNull($user->getLocale());
        self::assertNull($user->getTimezone());
        self::assertNull($user->getCreatedAt());
        self::assertNull($user->getUpdatedAt());
        self::assertNull($user->getConfirmationToken());
        self::assertNull($user->getPasswordRequestedAt());
        self::assertCount(0, $user->getGroups());
    }

    public function testSettersAndGetters(): void
    {
        $user = new User();

        $user->setEmail('test@example.com');
        self::assertSame('test@example.com', $user->getEmail());

        $user->setPassword('hashed');
        self::assertSame('hashed', $user->getPassword());

        $user->setPlainPassword('plain');
        self::assertSame('plain', $user->getPlainPassword());

        $user->setEnabled(true);
        self::assertTrue($user->isEnabled());

        $user->setFirstname('John');
        self::assertSame('John', $user->getFirstname());

        $user->setLastname('Doe');
        self::assertSame('Doe', $user->getLastname());

        $user->setPhone('+1234567890');
        self::assertSame('+1234567890', $user->getPhone());

        $user->setLocale('en');
        self::assertSame('en', $user->getLocale());

        $user->setTimezone('Europe/Berlin');
        self::assertSame('Europe/Berlin', $user->getTimezone());

        $now = new \DateTimeImmutable();
        $user->setCreatedAt($now);
        self::assertSame($now, $user->getCreatedAt());

        $user->setUpdatedAt($now);
        self::assertSame($now, $user->getUpdatedAt());

        $user->setConfirmationToken('token123');
        self::assertSame('token123', $user->getConfirmationToken());

        $user->setPasswordRequestedAt($now);
        self::assertSame($now, $user->getPasswordRequestedAt());
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        self::assertSame('test@example.com', $user->getUserIdentifier());
    }

    public function testGetRolesMergesGroupRolesAndRoleUser(): void
    {
        $user = new User();
        $user->setRealRoles(['ROLE_ADMIN']);

        $group = new Group();
        $group->setRoles(['ROLE_EDITOR']);
        $user->addGroup($group);

        $roles = $user->getRoles();

        self::assertContains('ROLE_ADMIN', $roles);
        self::assertContains('ROLE_EDITOR', $roles);
        self::assertContains('ROLE_USER', $roles);
    }

    public function testGetRolesDeduplicates(): void
    {
        $user = new User();
        $user->setRealRoles(['ROLE_USER', 'ROLE_ADMIN']);

        $roles = $user->getRoles();

        self::assertSame(array_unique($roles), $roles);
    }

    public function testRealRoles(): void
    {
        $user = new User();
        $user->setRealRoles(['ROLE_ADMIN', 'ROLE_EDITOR']);

        self::assertSame(['ROLE_ADMIN', 'ROLE_EDITOR'], $user->getRealRoles());
    }

    public function testIsSuperAdmin(): void
    {
        $user = new User();
        self::assertFalse($user->isSuperAdmin());

        $user->setSuperAdmin(true);
        self::assertTrue($user->isSuperAdmin());
        self::assertContains(UserInterface::ROLE_SUPER_ADMIN, $user->getRealRoles());

        $user->setSuperAdmin(false);
        self::assertFalse($user->isSuperAdmin());
        self::assertNotContains(UserInterface::ROLE_SUPER_ADMIN, $user->getRealRoles());
    }

    public function testSetSuperAdminDoesNotDuplicate(): void
    {
        $user = new User();
        $user->setSuperAdmin(true);
        $user->setSuperAdmin(true);

        $count = array_count_values($user->getRealRoles());
        self::assertSame(1, $count[UserInterface::ROLE_SUPER_ADMIN]);
    }

    public function testFullname(): void
    {
        $user = new User();
        self::assertSame('', $user->getFullname());

        $user->setFirstname('John');
        self::assertSame('John', $user->getFullname());

        $user->setLastname('Doe');
        self::assertSame('John Doe', $user->getFullname());
    }

    public function testToString(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        self::assertSame('test@example.com', (string) $user);

        $user->setFirstname('John');
        $user->setLastname('Doe');
        self::assertSame('John Doe', (string) $user);
    }

    public function testIsPasswordRequestNonExpired(): void
    {
        $user = new User();

        // No request date set
        self::assertFalse($user->isPasswordRequestNonExpired(3600));

        // Request date in the past, expired
        $user->setPasswordRequestedAt(new \DateTimeImmutable('-2 hours'));
        self::assertFalse($user->isPasswordRequestNonExpired(3600));

        // Request date recent, not expired
        $user->setPasswordRequestedAt(new \DateTimeImmutable());
        self::assertTrue($user->isPasswordRequestNonExpired(3600));
    }

    public function testGroupManagement(): void
    {
        $user = new User();
        $group = new Group();

        self::assertFalse($user->hasGroup($group));

        $user->addGroup($group);
        self::assertTrue($user->hasGroup($group));
        self::assertCount(1, $user->getGroups());

        // Adding same group again should not duplicate
        $user->addGroup($group);
        self::assertCount(1, $user->getGroups());

        $user->removeGroup($group);
        self::assertFalse($user->hasGroup($group));
        self::assertCount(0, $user->getGroups());
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        $user->setPlainPassword('secret');
        self::assertSame('secret', $user->getPlainPassword());

        $user->eraseCredentials();
        self::assertNull($user->getPlainPassword());
    }
}
