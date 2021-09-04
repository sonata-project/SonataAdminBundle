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
            'html5_validate' => true,
            'lock_protection' => false,
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
        static::assertSame('group', $this->configuration->getOption('default_group', 'group'));
    }
}
