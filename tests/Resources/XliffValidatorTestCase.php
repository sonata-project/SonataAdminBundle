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

namespace Sonata\AdminBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Loader\XliffFileLoader;

abstract class XliffValidatorTestCase extends TestCase
{
    /**
     * @var XliffFileLoader
     */
    protected $loader;

    /**
     * @var string[]
     */
    protected $errors = [];

    protected function setUp(): void
    {
        $this->loader = new XliffFileLoader();
    }

    /**
     * @dataProvider getXliffPaths
     *
     * @phpstan-param non-empty-string $path
     */
    public function testXliff(string $path): void
    {
        $this->validatePath($path);

        if (\count($this->errors) > 0) {
            static::fail(sprintf('Unable to parse xliff files: %s', implode(', ', $this->errors)));
        }

        static::assertCount(
            0,
            $this->errors,
            sprintf('Unable to parse xliff files: %s', implode(', ', $this->errors))
        );
    }

    /**
     * @return array<array{string}>
     *
     * @phpstan-return array<array{non-empty-string}>
     */
    abstract public function getXliffPaths(): array;

    protected function validateXliff(string $file): void
    {
        try {
            $this->loader->load($file, 'en');
            static::assertTrue(true, sprintf('Successful loading file: %s', $file));
        } catch (InvalidResourceException $e) {
            $this->errors[] = sprintf('%s => %s', $file, $e->getMessage());
        }
    }

    /**
     * @phpstan-param non-empty-string $path
     */
    protected function validatePath(string $path): void
    {
        $files = glob(sprintf('%s/*.xliff', $path));
        static::assertIsArray($files);

        foreach ($files as $file) {
            $this->validateXliff($file);
        }
    }
}
