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

namespace SensioLabs\AdminBundle\Tests\User\DependencyInjection;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

final class UserConfigurationTest extends TestCase
{
    public function testDefaultUserConfiguration(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), []);

        self::assertArrayHasKey('user', $config);
        self::assertFalse($config['user']['enabled']);
    }

    public function testEnabledUserConfiguration(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [
            [
                'user' => [
                    'enabled' => true,
                ],
            ],
        ]);

        self::assertTrue($config['user']['enabled']);
        self::assertSame('App\\Entity\\User', $config['user']['class']['user']);
        self::assertSame('App\\Entity\\Group', $config['user']['class']['group']);
    }

    public function testCustomUserClassConfiguration(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [
            [
                'user' => [
                    'enabled' => true,
                    'class' => [
                        'user' => 'My\\Entity\\User',
                        'group' => 'My\\Entity\\Group',
                    ],
                ],
            ],
        ]);

        self::assertSame('My\\Entity\\User', $config['user']['class']['user']);
        self::assertSame('My\\Entity\\Group', $config['user']['class']['group']);
    }

    public function testResettingDefaults(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [
            [
                'user' => [
                    'enabled' => true,
                ],
            ],
        ]);

        self::assertSame(86400, $config['user']['resetting']['ttl']);
        self::assertSame('noreply@example.com', $config['user']['resetting']['from_email']);
        self::assertSame(
            '@SensioLabsAdmin/User/Email/reset_password.html.twig',
            $config['user']['resetting']['email_template'],
        );
    }

    public function testProfileDefaults(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [
            [
                'user' => [
                    'enabled' => true,
                ],
            ],
        ]);

        self::assertSame('bundles/sensiolabsadmin/default_avatar.png', $config['user']['profile']['default_avatar']);
    }

    public function testAdminDefaults(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [
            [
                'user' => [
                    'enabled' => true,
                ],
            ],
        ]);

        self::assertSame('SensioLabs\\AdminBundle\\User\\Admin\\UserAdmin', $config['user']['admin']['user']['class']);
        self::assertSame('SensioLabs\\AdminBundle\\User\\Admin\\GroupAdmin', $config['user']['admin']['group']['class']);
        self::assertSame('sensiolabs.admin.controller.crud', $config['user']['admin']['user']['controller']);
    }
}
