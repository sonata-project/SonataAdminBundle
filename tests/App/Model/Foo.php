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
    private string $id;

    private string $name;

    /**
     * @var string[]
     */
    private array $elements;

    private ?Bar $referenced;

    /**
     * @param string[] $elements
     */
    public function __construct(string $id, string $name, array $elements = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->elements = $elements;
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
