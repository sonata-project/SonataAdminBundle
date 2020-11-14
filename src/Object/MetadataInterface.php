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

interface MetadataInterface
{
    public function getTitle(): string;

    public function getDescription(): ?string;

    public function getImage(): ?string;

    public function getDomain(): ?string;

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array;

    /**
     * @param string $name    The option key
     * @param mixed  $default The default value if option not found
     *
     * @return mixed
     */
    public function getOption($name, $default = null);
}
