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

namespace SensioLabs\AdminBundle\Tests\User\Form\Type;

use Psr\Container\ContainerInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\User\Form\Type\SecurityRolesType;
use SensioLabs\AdminBundle\User\Security\EditableRolesBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class SecurityRolesTypeTest extends TypeTestCase
{
    private EditableRolesBuilder $roleBuilder;

    public function testGetDefaultOptions(): void
    {
        $type = new SecurityRolesType($this->roleBuilder);

        $optionResolver = new OptionsResolver();
        $type->configureOptions($optionResolver);

        $options = $optionResolver->resolve();
        self::assertCount(3, $options['choices']);
    }

    public function testGetParent(): void
    {
        $type = new SecurityRolesType($this->roleBuilder);

        self::assertSame(ChoiceType::class, $type->getParent());
    }

    public function testSubmitValidData(): void
    {
        $form = $this->factory->create(SecurityRolesType::class, null, [
            'multiple' => true,
            'expanded' => true,
            'required' => false,
        ]);

        $form->submit([0 => 'ROLE_FOO']);

        self::assertTrue($form->isValid());
        self::assertCount(1, $form->getData());
        self::assertContains('ROLE_FOO', $form->getData());
    }

    public function testSubmitInvalidData(): void
    {
        $form = $this->factory->create(SecurityRolesType::class, null, [
            'multiple' => true,
            'expanded' => true,
            'required' => false,
        ]);

        $form->submit([0 => 'ROLE_NOT_EXISTS']);

        self::assertFalse($form->isValid());
        self::assertSame([], $form->getData());
    }

    public function testSubmitWithHiddenRoleData(): void
    {
        $originalRoles = ['ROLE_SUPER_ADMIN', 'ROLE_USER'];

        $form = $this->factory->create(SecurityRolesType::class, $originalRoles, [
            'multiple' => true,
            'expanded' => true,
            'required' => false,
        ]);

        // Submit only ROLE_USER but ROLE_SUPER_ADMIN should be preserved (hidden role)
        $form->submit([0 => 'ROLE_USER']);

        self::assertNull($form->getTransformationFailure());
        self::assertTrue($form->isValid());
        self::assertCount(2, $form->getData());
        self::assertContains('ROLE_SUPER_ADMIN', $form->getData());
    }

    public function testBlockPrefix(): void
    {
        $type = new SecurityRolesType($this->roleBuilder);

        self::assertSame('sensiolabs_security_roles', $type->getBlockPrefix());
    }

    protected function getExtensions(): array
    {
        $pool = new Pool($this->createMock(ContainerInterface::class));

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($this->createMock(TokenInterface::class));

        // ROLE_SUPER_ADMIN is not granted (read-only/hidden), so the transformer preserves it
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')
            ->willReturnCallback(static fn (string $role): bool => 'ROLE_SUPER_ADMIN' !== $role);

        $this->roleBuilder = new EditableRolesBuilder($pool, $tokenStorage, $authChecker, [
            'ROLE_FOO' => [],
            'ROLE_USER' => [],
            'ROLE_SUPER_ADMIN' => [],
        ]);

        $childType = new SecurityRolesType($this->roleBuilder);

        return [new PreloadedExtension([
            $childType,
        ], [])];
    }
}
