<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\App\Model;

/**
 * @phpstan-implements RepositoryInterface<Foo>
 */
final class FooRepository implements RepositoryInterface
{
    /**
     * @var Foo[]
     */
    private array $elements;

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
