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

namespace Sonata\AdminBundle\Tests\App\Model;

final class Foo implements EntityInterface
{
    private ?Bar $referenced;

    /**
     * @param string[] $elements
     */
    public function __construct(
        private string $id,
        private string $name,
        private array $elements = [],
    ) {
        $this->referenced = null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setReferenced(?Bar $bar): void
    {
        $this->referenced = $bar;
    }

    public function getReferenced(): ?Bar
    {
        return $this->referenced;
    }

    /**
     * @return string[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }
}
