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

/**
 * @phpstan-implements RepositoryInterface<Baz>
 */
final class BazRepository implements RepositoryInterface
{
    /**
     * @var Baz[]
     */
    private array $elements;

    public function __construct()
    {
        $this->elements = [
            'test_id' => new Baz('test_id'),
        ];
    }

    public function byId(string $id): ?Baz
    {
        return $this->elements[$id] ?? null;
    }

    /**
     * @return Baz[]
     */
    public function all(): array
    {
        return $this->elements;
    }
}
