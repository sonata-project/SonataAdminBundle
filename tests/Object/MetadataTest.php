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

namespace Sonata\AdminBundle\Tests\Object;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Object\Metadata;

class MetadataTest extends TestCase
{
    public function testGetters(): void
    {
        $metadata = new Metadata('title', 'description', 'image', 'domain', ['key1' => 'value1']);

        self::assertSame('title', $metadata->getTitle());
        self::assertSame('description', $metadata->getDescription());
        self::assertSame('image', $metadata->getImage());
        self::assertSame('domain', $metadata->getDomain());

        self::assertSame('value1', $metadata->getOption('key1'));
        self::assertSame('valueDefault', $metadata->getOption('none', 'valueDefault'));
        self::assertNull($metadata->getOption('none'));
        self::assertSame(['key1' => 'value1'], $metadata->getOptions());
        self::assertSame('value1', $metadata->getOption('key1'));

        $metadata2 = new Metadata('title', 'description', 'image');
        self::assertNull($metadata2->getDomain());
        self::assertSame([], $metadata2->getOptions());
    }

    public function testImageNullGetDefaultImage(): void
    {
        $metadata = new Metadata('title', 'description');
        self::assertSame($metadata::DEFAULT_MOSAIC_BACKGROUND, $metadata->getImage());
    }

    /**
     * @dataProvider isImageAvailableProvider
     */
    public function testIsImageAvailable(bool $expected, ?string $image): void
    {
        self::assertSame(
            $expected,
            (new Metadata('title', 'description', $image))->isImageAvailable()
        );
    }

    /**
     * @phpstan-return iterable<array-key, array{bool, string|null}>
     */
    public function isImageAvailableProvider(): iterable
    {
        yield 'image is null' => [false, null];
        yield 'image is available' => [true, 'image.png'];
    }
}
