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
     * @var array<string, string>
     */
    private array $elements = [];

    /**
     * @var string[]
     */
    private array $collection = [];

    public function __construct(
        private string $id,
        private string $name,
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
     * @param array<string, string> $elements
     */
    public function setElements(array $elements): void
    {
        $this->elements = $elements;
    }

    /**
     * @return array<string, string>
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * @param string[] $collection
     */
    public function setCollection(array $collection): void
    {
        $this->collection = $collection;
    }

    /**
     * @return string[]
     */
    public function getCollection(): array
    {
        return $this->collection;
    }
}
