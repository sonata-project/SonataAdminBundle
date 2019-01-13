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

namespace Sonata\AdminBundle\Object;

use PHPUnit\Framework\TestCase;

class MetadataTest extends TestCase
{
    public function testGetters(): void
    {
        $metadata = new Metadata('title', 'description', 'image', 'domain', ['key1' => 'value1']);

        $this->assertSame('title', $metadata->getTitle());
        $this->assertSame('description', $metadata->getDescription());
        $this->assertSame('image', $metadata->getImage());
        $this->assertSame('domain', $metadata->getDomain());

        $this->assertSame('value1', $metadata->getOption('key1'));
        $this->assertSame('valueDefault', $metadata->getOption('none', 'valueDefault'));
        $this->assertNull($metadata->getOption('none'));
        $this->assertSame(['key1' => 'value1'], $metadata->getOptions());
        $this->assertSame('value1', $metadata->getOption('key1'));

        $metadata2 = new Metadata('title', 'description', 'image');
        $this->assertNull($metadata2->getDomain());
        $this->assertSame([], $metadata2->getOptions());
    }

    public function testImageNullGetDefaultImage(): void
    {
        $metadata = new Metadata('title', 'description');
        $this->assertSame($metadata::DEFAULT_MOSAIC_BACKGROUND, $metadata->getImage());
    }

    /**
     * @dataProvider isImageAvailableProvider
     */
    public function testIsImageAvailable(bool $expected, ?string $image): void
    {
        $this->assertSame(
            $expected,
            (new Metadata('title', 'description', $image))->isImageAvailable()
        );
    }

    public function isImageAvailableProvider(): \Generator
    {
        yield 'image is null' => [false, null];
        yield 'image is available' => [true, 'image.png'];
    }
}
