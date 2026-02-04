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

namespace SensioLabs\AdminBundle\Tests\Asset;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Asset\LastModifiedVersionStrategy;

final class LastModifiedVersionStrategyTest extends TestCase
{
    public function testApplyVersion(): void
    {
        $projectDir = sys_get_temp_dir();
        $strategy = new LastModifiedVersionStrategy($projectDir, '/');

        touch($projectDir.'/asset.js', $mtime = time());
        static::assertSame('asset.js?v='.$mtime, $strategy->applyVersion('asset.js'));
        static::assertSame('asset.js?foo=bar&v='.$mtime, $strategy->applyVersion('asset.js?foo=bar'));
    }
}
