<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\Type;

use Sonata\AdminBundle\Form\Type\AclMatrixType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Baptiste Meyer <baptiste@les-tilleuls.coop>
 */
class AclMatrixTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions()
    {
        $type = new AclMatrixType();
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');

        $permissions = array(
            'OWNER' => array(
                'required' => false,
                'data'     => false,
                'disabled' => false,
                'attr'     => array(),
            ),
        );

        $optionResolver = new OptionsResolver();

        if (!method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $type->setDefaultOptions($optionResolver);
        } else {
            $type->configureOptions($optionResolver);
        }

        $options = $optionResolver->resolve(array(
            'acl_value'   => $user,
            'permissions' => $permissions,
        ));

        $this->assertInstanceOf('Symfony\Component\Security\Core\User\UserInterface', $options['acl_value']);
        $this->assertSame($user, $options['acl_value']);
        $this->assertSame($permissions, $options['permissions']);
    }
}
