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

namespace Sonata\AdminBundle\Tests;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\SonataConfiguration;

final class SonataConfigurationTest extends TestCase
{
    /**
     * @var SonataConfiguration
     */
    private $configuration;

    protected function setUp(): void
    {
        $this->configuration = new SonataConfiguration('title', '/path/to/logo.png', [
            'confirm_exit' => true,
            'default_group' => 'default',
            'default_icon' => '<i class="fas fa-folder"></i>',
            'default_label_catalogue' => 'SonataAdminBundle',
            'dropdown_number_groups_per_colums' => 2,
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
            'search' => true,
            'skin' => 'black',
            'sort_admins' => true,
            'stylesheets' => [],
            'use_bootlint' => false,
            'use_icheck' => true,
            'use_select2' => true,
            'use_stickyforms' => false,
        ]);
    }

    public function testGetTitle(): void
    {
        self::assertSame('title', $this->configuration->getTitle());
    }

    public function testGetLogo(): void
    {
        self::assertSame('/path/to/logo.png', $this->configuration->getLogo());
    }

    public function testGetOption(): void
    {
        self::assertTrue($this->configuration->getOption('html5_validate'));
        self::assertFalse($this->configuration->getOption('lock_protection'));
    }

    public function testGetOptionDefault(): void
    {
        self::assertNull($this->configuration->getOption('pager_links'));
        self::assertSame(1, $this->configuration->getOption('pager_links', 1));
    }
}
