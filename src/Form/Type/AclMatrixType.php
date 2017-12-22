<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * This type define an ACL matrix.
 *
 * @author Samuel Roze <samuel@sroze.io>
 * @author Baptiste Meyer <baptiste@les-tilleuls.coop>
 */
class AclMatrixType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $aclValueType = $options['acl_value'] instanceof UserInterface ? 'user' : 'role';
        $aclValueData = $options['acl_value'] instanceof UserInterface ? $options['acl_value']->getUsername() : $options['acl_value'];

        $builder->add($aclValueType, HiddenType::class, [
            'data' => $aclValueData,
        ]);

        foreach ($options['permissions'] as $permission => $attributes) {
            $builder->add($permission, CheckboxType::class, $attributes);
        }
    }

    /**
     * NEXT_MAJOR: Remove method, when bumping requirements to SF 2.7+.
     *
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['permissions', 'acl_value']);
        $resolver->setAllowedTypes('permissions', 'array');
        $resolver->setAllowedTypes('acl_value', ['string', UserInterface::class]);
    }

    /**
     * NEXT_MAJOR: Remove when dropping Symfony <2.8 support.
     *
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'sonata_type_acl_matrix';
    }
}
