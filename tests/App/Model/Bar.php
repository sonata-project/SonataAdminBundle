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

final class Bar implements EntityInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Foo|null
     */
    private $foo;

    public function __construct(string $id, ?Foo $foo = null)
    {
        $this->id = $id;
        $this->foo = $foo;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFoo(): ?Foo
    {
        return $this->foo;
    }
}
