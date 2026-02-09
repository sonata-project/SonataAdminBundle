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

use SensioLabs\AdminBundle\User\Form\Type\RolesMatrixType;
use SensioLabs\AdminBundle\User\Security\RolesBuilder\ExpandableRolesBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RolesMatrixTypeTest extends TypeTestCase
{
    private ExpandableRolesBuilderInterface $roleBuilder;

    public function testGetDefaultOptions(): void
    {
        $type = new RolesMatrixType($this->roleBuilder);

        $optionResolver = new OptionsResolver();
        $type->configureOptions($optionResolver);

        $options = $optionResolver->resolve();
        self::assertCount(3, $options['choices']);
    }

    public function testGetParent(): void
    {
        $type = new RolesMatrixType($this->roleBuilder);

        self::assertSame(ChoiceType::class, $type->getParent());
    }

    public function testSubmitValidData(): void
    {
        $form = $this->factory->create(RolesMatrixType::class, null, [
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
        $form = $this->factory->create(RolesMatrixType::class, null, [
            'multiple' => true,
            'expanded' => true,
            'required' => false,
        ]);

        $form->submit([0 => 'ROLE_NOT_EXISTS']);

        self::assertFalse($form->isValid());
        self::assertSame([], $form->getData());
    }

    public function testBlockPrefix(): void
    {
        $type = new RolesMatrixType($this->roleBuilder);

        self::assertSame('sensiolabs_roles_matrix', $type->getBlockPrefix());
    }

    protected function getExtensions(): array
    {
        $this->roleBuilder = $this->createMock(ExpandableRolesBuilderInterface::class);

        $this->roleBuilder->method('getExpandedRoles')->willReturn([
            'ROLE_FOO' => ['role' => 'ROLE_FOO', 'is_granted' => true],
            'ROLE_USER' => ['role' => 'ROLE_USER', 'is_granted' => true],
            'ROLE_ADMIN' => ['role' => 'ROLE_ADMIN', 'is_granted' => false],
        ]);

        $childType = new RolesMatrixType($this->roleBuilder);

        return [new PreloadedExtension([
            $childType,
        ], [])];
    }
}
