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

namespace Sonata\AdminBundle\Tests\Form\Type;

use Sonata\AdminBundle\Form\Type\AclMatrixType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Baptiste Meyer <baptiste@les-tilleuls.coop>
 */
class AclMatrixTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions()
    {
        $type = new AclMatrixType();
        $user = $this->getMockForAbstractClass(UserInterface::class);

        $permissions = [
            'OWNER' => [
                'required' => false,
                'data' => false,
                'disabled' => false,
                'attr' => [],
            ],
        ];

        $optionResolver = new OptionsResolver();

        $type->configureOptions($optionResolver);

        $options = $optionResolver->resolve([
            'acl_value' => $user,
            'permissions' => $permissions,
        ]);

        $this->assertInstanceOf(UserInterface::class, $options['acl_value']);
        $this->assertSame($user, $options['acl_value']);
        $this->assertSame($permissions, $options['permissions']);
    }
}
