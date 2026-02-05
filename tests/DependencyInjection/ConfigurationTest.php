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

namespace SensioLabs\AdminBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\DependencyInjection\Configuration;
use SensioLabs\AdminBundle\Tests\Fixtures\Controller\FooAdminController;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testOptions(): void
    {
        $config = $this->process([]);

        static::assertIsArray($config['options']);
        static::assertTrue($config['options']['html5_validate']);
        static::assertNull($config['options']['pager_links']);
        static::assertTrue($config['options']['confirm_exit']);
        static::assertFalse($config['options']['js_debug']);
        static::assertSame('bundles/sonataadmin/images/default_mosaic_image.png', $config['options']['mosaic_background']);
        static::assertSame('messages', $config['options']['default_translation_domain']);
        static::assertSame('lucide:folder', $config['options']['default_icon']);
    }

    public function testBreadcrumbsChildRouteDefaultsToShow(): void
    {
        $config = $this->process([]);

        static::assertIsArray($config['breadcrumbs']);
        static::assertSame('show', $config['breadcrumbs']['child_admin_route']);
    }

    public function testOptionsWithInvalidFormat(): void
    {
        $this->expectException(InvalidTypeException::class);

        $this->process([[
            'options' => [
                'html5_validate' => '1',
            ],
        ]]);
    }

    public function testDefaultAdminServicesDefault(): void
    {
        $config = $this->process([[
            'default_admin_services' => [],
        ]]);

        static::assertSame([
            'model_manager' => null,
            'data_source' => null,
            'field_description_factory' => null,
            'form_contractor' => null,
            'show_builder' => null,
            'list_builder' => null,
            'datagrid_builder' => null,
            'translator' => null,
            'configuration_pool' => null,
            'route_generator' => null,
            'security_handler' => null,
            'menu_factory' => null,
            'route_builder' => null,
            'label_translator_strategy' => null,
            'pager_type' => null,
        ], $config['default_admin_services']);
    }

    public function testSecurityConfigurationDefaults(): void
    {
        $config = $this->process([[]]);

        static::assertIsArray($config['security']);
        static::assertSame('ROLE_SONATA_ADMIN', $config['security']['role_admin']);
        static::assertSame('ROLE_SUPER_ADMIN', $config['security']['role_super_admin']);
    }

    public function testExtraAssetsDefaults(): void
    {
        $config = $this->process([[]]);

        static::assertIsArray($config['assets']);
        static::assertSame([], $config['assets']['extra_stylesheets']);
        static::assertSame([], $config['assets']['extra_javascripts']);
    }

    public function testRemoveAssetsDefaults(): void
    {
        $config = $this->process([[]]);

        static::assertIsArray($config['assets']);
        static::assertSame([], $config['assets']['remove_stylesheets']);
        static::assertSame([], $config['assets']['remove_javascripts']);
    }

    public function testNormalizationForAssetNodes(): void
    {
        $config = $this->process([[
            'assets' => [
                'extra_stylesheets' => [
                    'foo.css',
                    ['path' => 'bar.css', 'package_name' => 'pkg'],
                    ['path' => 'baz.css', 'package_name' => null],
                ],
                'extra_javascripts' => [
                    'foo.js',
                    ['path' => 'bar.js', 'package_name' => 'pkg'],
                    ['path' => 'baz.js', 'package_name' => null],
                ],
            ],
        ]]);

        static::assertSame([
            ['path' => 'foo.css', 'package_name' => 'sensiolabs_admin'],
            ['path' => 'bar.css', 'package_name' => 'pkg'],
            ['path' => 'baz.css', 'package_name' => null],
        ], $config['assets']['extra_stylesheets']);

        static::assertSame([
            ['path' => 'foo.js', 'package_name' => 'sensiolabs_admin'],
            ['path' => 'bar.js', 'package_name' => 'pkg'],
            ['path' => 'baz.js', 'package_name' => null],
        ], $config['assets']['extra_javascripts']);
    }

    public function testAssetWithWrongType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid "stylesheets" item type. String or associative array are allowed.');

        $this->process([[
            'assets' => [
                'stylesheets' => [
                    'foo.css',
                    null,
                ],
            ],
        ]]);
    }

    public function testAssetWithNullPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "path" key of the "extra_stylesheets" item can not be null.');

        $this->process([[
            'assets' => [
                'extra_stylesheets' => [
                    'foo.css',
                    ['path' => null, 'package_name' => 'pkg'],
                    ['path' => 'bar.css', 'package_name' => null],
                ],
            ],
        ]]);
    }

    public function testAssetWithNoPackageName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "javascripts" item with array form must contain the "path" and the "package_name" keys.');

        $this->process([[
            'assets' => [
                'javascripts' => [
                    ['path' => 'bar.js'],
                ],
            ],
        ]]);
    }

    public function testDefaultControllerIsCRUDController(): void
    {
        $config = $this->process([]);

        static::assertSame('sensiolabs.admin.controller.crud', $config['default_controller']);
    }

    public function testSettingDefaultController(): void
    {
        $config = $this->process([[
            'default_controller' => FooAdminController::class,
        ]]);

        static::assertSame(FooAdminController::class, $config['default_controller']);
    }

    /**
     * Processes an array of configurations and returns a compiled version.
     *
     * @param array<array<string, mixed>> $configs An array of raw configurations
     *
     * @return array<string, mixed> A normalized array
     */
    protected function process($configs): array
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }
}
