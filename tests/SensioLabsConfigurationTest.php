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

namespace SensioLabs\AdminBundle\Tests;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\SensioLabsConfiguration;

final class SensioLabsConfigurationTest extends TestCase
{
    private SensioLabsConfiguration $configuration;

    protected function setUp(): void
    {
        $this->configuration = new SensioLabsConfiguration('title', '/path/to/logo.png', [
            'confirm_exit' => true,
            'default_admin_route' => 'show',
            'default_icon' => 'lucide:folder',
            'default_translation_domain' => 'messages',
            'form_type' => 'standard',
            'html5_validate' => true,
            'javascripts' => [],
            'js_debug' => false,
            'list_action_button_content' => 'all',
            'lock_protection' => false,
            'logo_content' => 'text',
            'mosaic_background' => 'bundles/sonataadmin/images/default_mosaic_image.png',
            'pager_links' => null,
            'role_admin' => 'ROLE_SONATA_ADMIN',
            'role_super_admin' => 'ROLE_SUPER_ADMIN',
            'stylesheets' => [],
            'use_select2' => true,
            'use_stickyforms' => false,
        ]);
    }

    public function testGetTitle(): void
    {
        static::assertSame('title', $this->configuration->getTitle());
    }

    public function testGetLogo(): void
    {
        static::assertSame('/path/to/logo.png', $this->configuration->getLogo());
    }

    public function testGetOption(): void
    {
        static::assertTrue($this->configuration->getOption('html5_validate'));
        static::assertFalse($this->configuration->getOption('lock_protection'));
    }

    public function testGetOptionDefault(): void
    {
        static::assertNull($this->configuration->getOption('pager_links'));
        static::assertSame(1, $this->configuration->getOption('pager_links', 1));
    }
}
