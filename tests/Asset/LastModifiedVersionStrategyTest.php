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

namespace Sonata\AdminBundle\Tests\Asset;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Asset\LastModifiedVersionStrategy;

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
