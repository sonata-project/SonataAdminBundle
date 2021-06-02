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

namespace Sonata\AdminBundle\Form\Type;

use Sonata\AdminBundle\BCLayer\BCUserInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * This type define an ACL matrix.
 *
 * @author Samuel Roze <samuel@sroze.io>
 * @author Baptiste Meyer <baptiste@les-tilleuls.coop>
 */
final class AclMatrixType extends AbstractType
{
    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $aclValueType = $options['acl_value'] instanceof UserInterface ? 'user' : 'role';
        $aclValueData = $options['acl_value'] instanceof UserInterface ? BCUserInterface::getUsername($options['acl_value']) : $options['acl_value'];

        $builder->add($aclValueType, HiddenType::class, [
            'data' => $aclValueData,
        ]);

        foreach ($options['permissions'] as $permission => $attributes) {
            $builder->add($permission, CheckboxType::class, $attributes);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['permissions', 'acl_value']);
        $resolver->setAllowedTypes('permissions', 'array');
        $resolver->setAllowedTypes('acl_value', ['string', UserInterface::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'sonata_type_acl_matrix';
    }
}
