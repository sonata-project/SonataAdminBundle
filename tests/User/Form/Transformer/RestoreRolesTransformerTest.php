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

namespace SensioLabs\AdminBundle\Tests\User\Form\Transformer;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\User\Form\Transformer\RestoreRolesTransformer;
use SensioLabs\AdminBundle\User\Security\EditableRolesBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class RestoreRolesTransformerTest extends TestCase
{
    public function testTransformReturnsEmptyArrayForNull(): void
    {
        $builder = $this->createEditableRolesBuilder();
        $transformer = new RestoreRolesTransformer($builder);

        self::assertSame([], $transformer->transform(null));
    }

    public function testTransformReturnsValueAsIs(): void
    {
        $builder = $this->createEditableRolesBuilder();
        $transformer = new RestoreRolesTransformer($builder);

        $roles = ['ROLE_ADMIN', 'ROLE_USER'];

        self::assertSame($roles, $transformer->transform($roles));
    }

    public function testReverseTransformMergesHiddenRoles(): void
    {
        // ROLE_HIDDEN is in hierarchy but not granted → read-only
        $builder = $this->createEditableRolesBuilder(
            ['ROLE_HIDDEN' => []],
            static fn (string $role): bool => false,
        );

        $transformer = new RestoreRolesTransformer($builder);
        $transformer->setOriginalRoles(['ROLE_ADMIN', 'ROLE_HIDDEN']);

        // User submits only ROLE_ADMIN (can't see ROLE_HIDDEN)
        $result = $transformer->reverseTransform(['ROLE_ADMIN']);

        self::assertContains('ROLE_ADMIN', $result);
        self::assertContains('ROLE_HIDDEN', $result);
    }

    public function testReverseTransformHandlesNullSubmission(): void
    {
        $builder = $this->createEditableRolesBuilder();
        $transformer = new RestoreRolesTransformer($builder);
        $transformer->setOriginalRoles([]);

        self::assertSame([], $transformer->reverseTransform(null));
    }

    public function testReverseTransformRevokingVisibleRoleWorks(): void
    {
        // All roles are granted, so nothing is read-only
        $builder = $this->createEditableRolesBuilder(
            ['ROLE_FOO' => [], 'ROLE_BAR' => [], 'ROLE_BAZ' => []],
            static fn (): bool => true,
        );

        $transformer = new RestoreRolesTransformer($builder);
        $transformer->setOriginalRoles(['ROLE_FOO', 'ROLE_BAR', 'ROLE_BAZ']);

        // User revokes ROLE_FOO by not submitting it
        $result = $transformer->reverseTransform(['ROLE_BAR', 'ROLE_BAZ']);

        self::assertNotContains('ROLE_FOO', $result);
        self::assertContains('ROLE_BAR', $result);
        self::assertContains('ROLE_BAZ', $result);
    }

    public function testReverseTransformPreservesHiddenRoleNotInAvailableRoles(): void
    {
        // ROLE_SUPER_ADMIN is read-only (the user can't see/edit it)
        $builder = $this->createEditableRolesBuilder(
            ['ROLE_USER' => [], 'ROLE_SUPER_ADMIN' => []],
            static fn (string $role): bool => 'ROLE_SUPER_ADMIN' !== $role,
        );

        $transformer = new RestoreRolesTransformer($builder);
        $transformer->setOriginalRoles(['ROLE_USER', 'ROLE_SUPER_ADMIN']);

        // User submits ROLE_USER + a new role ROLE_SONATA_ADMIN
        // ROLE_SUPER_ADMIN not submitted (hidden from user)
        $result = $transformer->reverseTransform(['ROLE_USER', 'ROLE_SONATA_ADMIN']);

        self::assertContains('ROLE_USER', $result);
        self::assertContains('ROLE_SONATA_ADMIN', $result);
        self::assertContains('ROLE_SUPER_ADMIN', $result, 'Hidden role should be preserved');
    }

    public function testReverseTransformWithNullOriginalRoles(): void
    {
        $builder = $this->createEditableRolesBuilder();

        $transformer = new RestoreRolesTransformer($builder);
        // setOriginalRoles(null) is converted to [] internally
        $transformer->setOriginalRoles(null);

        $result = $transformer->reverseTransform(['ROLE_FOO']);

        self::assertSame(['ROLE_FOO'], $result);
    }

    public function testReverseTransformDeduplicatesRoles(): void
    {
        // ROLE_HIDDEN is not granted → read-only
        $builder = $this->createEditableRolesBuilder(
            ['ROLE_HIDDEN' => []],
            static fn (): bool => false,
        );

        $transformer = new RestoreRolesTransformer($builder);
        $transformer->setOriginalRoles(['ROLE_HIDDEN']);

        // User somehow submits ROLE_HIDDEN too (shouldn't happen, but test dedup)
        $result = $transformer->reverseTransform(['ROLE_FOO', 'ROLE_HIDDEN']);

        self::assertCount(2, $result);
        self::assertContains('ROLE_FOO', $result);
        self::assertContains('ROLE_HIDDEN', $result);
    }

    /**
     * @param array<string, list<string>> $hierarchy
     * @param (\Closure(string): bool)|null $isGrantedCallback
     */
    private function createEditableRolesBuilder(
        array $hierarchy = [],
        ?\Closure $isGrantedCallback = null,
    ): EditableRolesBuilder {
        $pool = new Pool($this->createMock(ContainerInterface::class));

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($this->createMock(TokenInterface::class));

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')
            ->willReturnCallback($isGrantedCallback ?? static fn (): bool => false);

        return new EditableRolesBuilder($pool, $tokenStorage, $authChecker, $hierarchy);
    }
}
