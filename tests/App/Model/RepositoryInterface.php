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
 * @phpstan-template T of EntityInterface
 */
interface RepositoryInterface
{
    /**
     * @return T|null
     */
    public function byId(string $id);

    /**
     * @return array<T>
     */
    public function all(): array;
}
