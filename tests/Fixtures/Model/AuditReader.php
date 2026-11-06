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

namespace Sonata\AdminBundle\Tests\Fixtures\Model;

use Sonata\AdminBundle\Model\AuditReaderInterface;

// NEXT_MAJOR: Add type declarations
final class AuditReader implements AuditReaderInterface
{
    public function find($className, $id, $revision): object
    {
        return new \stdClass();
    }

    public function findRevisionHistory($className, $limit = 20, $offset = 0): array
    {
        return [
            new \stdClass(),
        ];
    }

    public function findRevision($classname, $revision): object
    {
        return new \stdClass();
    }

    public function findRevisions($className, $id): array
    {
        return [
            new \stdClass(),
        ];
    }

    public function diff($className, $id, $oldRevision, $newRevision): array
    {
        return [];
    }
}
