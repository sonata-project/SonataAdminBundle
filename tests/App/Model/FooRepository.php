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

final class FooRepository implements RepositoryInterface
{
    /**
     * @var array<class-string, Foo>
     */
    private $elements;

    public function __construct()
    {
        $this->elements = [
            'test_id' => new Foo('test_id', 'foo_name'),
        ];
    }

    public function byId(string $id): ?Foo
    {
        return $this->elements[$id] ?? null;
    }

    /**
     * @return Foo[]
     */
    public function all(): array
    {
        return $this->elements;
    }
}
