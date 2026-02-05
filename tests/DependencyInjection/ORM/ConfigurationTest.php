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

namespace SensioLabs\AdminBundle\Tests\DependencyInjection\ORM;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\DependencyInjection\ORM\Configuration;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testDefaultOptions(): void
    {
        $config = $this->process([]);

        static::assertArrayHasKey('entity_manager', $config);
        static::assertNull($config['entity_manager']);

        static::assertArrayHasKey('audit', $config);
        static::assertIsArray($config['audit']);
        static::assertArrayHasKey('force', $config['audit']);
        static::assertTrue($config['audit']['force']);

        static::assertArrayHasKey('templates', $config);
        static::assertIsArray($config['templates']);
        static::assertArrayNotHasKey('types', $config['templates']);
    }

    public function testAuditForceWithInvalidFormat(): void
    {
        $this->expectException(InvalidTypeException::class);

        $this->process([[
            'audit' => [
                'force' => '1',
            ],
        ]]);
    }

    public function testCustomTemplates(): void
    {
        $config = $this->process([[
            'templates' => [
                'types' => [
                    'list' => [
                        'array' => 'list_array.twig.html',
                    ],
                    'show' => [
                        'array' => 'show_array.twig.html',
                    ],
                ],
            ],
        ]]);

        static::assertArrayHasKey('templates', $config);
        static::assertIsArray($config['templates']);
        static::assertArrayHasKey('types', $config['templates']);
        static::assertSame([
            'list' => [
                'array' => 'list_array.twig.html',
            ],
            'show' => [
                'array' => 'show_array.twig.html',
            ],
        ], $config['templates']['types']);
    }

    public function testTemplateTypesWithInvalidValues(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->process([[
            'templates' => [
                'types' => [
                    'edit' => [],
                ],
            ],
        ]]);
    }

    /**
     * Processes an array of configurations and returns a compiled version.
     *
     * @param mixed[] $configs An array of raw configurations
     *
     * @return mixed[] A normalized array
     */
    protected function process($configs)
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }
}
