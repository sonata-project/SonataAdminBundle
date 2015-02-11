<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sonata\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * This type define an ACL matrix
 *
 * @author Samuel Roze <samuel@sroze.io>
 * @author Baptiste Meyer <baptiste@les-tilleuls.coop>
 */
class AclMatrixType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $aclValueType = $options['acl_value'] instanceof UserInterface ? 'user' : 'role';
        $aclValueData = $options['acl_value'] instanceof UserInterface ? $options['acl_value']->getUsername() : $options['acl_value'];

        $builder->add($aclValueType, 'hidden', array('data' => $aclValueData));

        foreach ($options['permissions'] as $permission => $attributes) {
            $builder->add($permission, 'checkbox', $attributes);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'permissions',
            'acl_value',
        ));

        $resolver->setAllowedTypes(array(
            'permissions' => 'array',
            'acl_value' => array('string', '\Symfony\Component\Security\Core\User\UserInterface'),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_type_acl_matrix';
    }
}
