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

final class Metadata implements MetadataInterface
{
    /**
     * @var string
     */
    public const DEFAULT_MOSAIC_BACKGROUND = 'bundles/sonataadmin/images/default_mosaic_image.png';

    /**
     * @var string
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $image;

    /**
     * @var string|null
     */
    private $domain;

    /**
     * @var array<string, mixed>
     */
    private $options = [];

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        string $title,
        ?string $description = null,
        ?string $image = null,
        ?string $domain = null,
        array $options = []
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->image = null !== $image ? $image : self::DEFAULT_MOSAIC_BACKGROUND;
        $this->domain = $domain;
        $this->options = $options;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function isImageAvailable(): bool
    {
        return null !== $this->image && self::DEFAULT_MOSAIC_BACKGROUND !== $this->image;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption($name, $default = null)
    {
        return \array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }
}
