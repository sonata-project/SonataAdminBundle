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

namespace SensioLabs\AdminBundle\User\Form\Type;

use SensioLabs\AdminBundle\User\Security\RolesBuilder\ExpandableRolesBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RolesMatrixType extends AbstractType
{
    public function __construct(
        private readonly ExpandableRolesBuilderInterface $rolesBuilder,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $roles = $this->rolesBuilder->getExpandedRoles();
        $choices = array_combine(array_keys($roles), array_keys($roles));

        $resolver->setDefaults([
            'choices' => false !== $choices ? $choices : [],
            'multiple' => true,
            'expanded' => true,
            'required' => false,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'sensiolabs_roles_matrix';
    }
}
