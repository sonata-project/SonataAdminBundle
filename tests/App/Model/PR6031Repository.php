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

final class PR6031Repository implements RepositoryInterface
{
    /**
     * @var array<class-string, PR6031>
     */
    private $elements = [];

    public function __construct()
    {
        $this->elements = [
            'pr_6031' => new PR6031('pr_6031', 'pr_6031'),
        ];
    }

    public function byId(string $id): ?PR6031
    {
        return $this->elements[$id] ?? null;
    }

    public function all(): array
    {
        return $this->elements;
    }
}
