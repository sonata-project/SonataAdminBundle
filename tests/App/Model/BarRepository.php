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
 * @phpstan-implements RepositoryInterface<Bar>
 */
final class BarRepository implements RepositoryInterface
{
    /**
     * @var Bar[]
     */
    private array $elements;

    public function __construct(
        FooRepository $fooRepository,
        BazRepository $bazRepository,
    ) {
        $this->elements = [
            'test_id' => new Bar('test_id', $fooRepository->byId('test_id'), $bazRepository->byId('test_id')),
        ];
    }

    public function byId(string $id): ?Bar
    {
        return $this->elements[$id] ?? null;
    }

    /**
     * @return Bar[]
     */
    public function all(): array
    {
        return $this->elements;
    }
}
