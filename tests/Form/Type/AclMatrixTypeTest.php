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

namespace SensioLabs\AdminBundle\Tests\Form\Type;

use SensioLabs\AdminBundle\Form\Type\AclMatrixType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Baptiste Meyer <baptiste@les-tilleuls.coop>
 */
final class AclMatrixTypeTest extends TypeTestCase
{
    public function testGetDefaultOptions(): void
    {
        $type = new AclMatrixType();
        $user = $this->createMock(UserInterface::class);

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

        static::assertInstanceOf(UserInterface::class, $options['acl_value']);
        static::assertSame($user, $options['acl_value']);
        static::assertSame($permissions, $options['permissions']);
    }
}
