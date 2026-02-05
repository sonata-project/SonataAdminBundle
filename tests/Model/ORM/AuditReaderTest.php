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

namespace SensioLabs\AdminBundle\Tests\Model\ORM;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SimpleThings\EntityAudit\AuditReader as SimpleThingsAuditReader;
use SensioLabs\AdminBundle\Model\ORM\AuditReader;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class AuditReaderTest extends TestCase
{
    /**
     * @var MockObject&SimpleThingsAuditReader
     */
    private SimpleThingsAuditReader $simpleThingsAuditReader;

    /**
     * @var AuditReader<object>
     */
    private AuditReader $auditReader;

    protected function setUp(): void
    {
        if (!class_exists(SimpleThingsAuditReader::class)) {
            static::markTestSkipped('AuditBundle is not available');
        }

        $this->simpleThingsAuditReader = $this->createMock(SimpleThingsAuditReader::class);
        $this->auditReader = new AuditReader($this->simpleThingsAuditReader);
    }

    public function testFind(): void
    {
        $className = \stdClass::class;
        $id = 1;
        $revision = 2;

        $this->simpleThingsAuditReader->expects(static::once())->method('find')->with($className, $id, $revision);

        $this->auditReader->find($className, $id, $revision);
    }

    public function testFindWithException(): void
    {
        $className = \stdClass::class;
        $id = 1;
        $revision = 2;

        $this->simpleThingsAuditReader
            ->expects(static::once())
            ->method('find')
            ->with($className, $id, $revision)
            ->willThrowException(new \Exception());

        static::assertNull($this->auditReader->find($className, $id, $revision));
    }

    public function testFindRevisionHistory(): void
    {
        $limit = 20;
        $offset = 0;

        $this->simpleThingsAuditReader
            ->expects(static::once())
            ->method('findRevisionHistory')
            ->with($limit, $offset)
            ->willReturn([]);

        $this->auditReader->findRevisionHistory(\stdClass::class, $limit, $offset);
    }

    public function testFindRevision(): void
    {
        $revision = 2;

        $this->simpleThingsAuditReader->expects(static::once())->method('findRevision')->with($revision);

        $this->auditReader->findRevision(\stdClass::class, $revision);
    }

    public function testFindRevisionWithException(): void
    {
        $revision = 2;

        $this->simpleThingsAuditReader
            ->expects(static::once())
            ->method('findRevision')
            ->with($revision)
            ->willThrowException(new \Exception());

        static::assertNull($this->auditReader->findRevision(\stdClass::class, $revision));
    }

    public function testFindRevisions(): void
    {
        $className = \stdClass::class;
        $id = 2;

        $this->simpleThingsAuditReader
            ->expects(static::once())
            ->method('findRevisions')
            ->with($className, $id)
            ->willReturn([]);

        $this->auditReader->findRevisions($className, $id);
    }

    public function testFindRevisionsWithException(): void
    {
        $className = \stdClass::class;
        $id = 2;

        $this->simpleThingsAuditReader
            ->expects(static::once())
            ->method('findRevisions')
            ->with($className, $id)
            ->willThrowException(new \Exception());

        static::assertSame([], $this->auditReader->findRevisions($className, $id));
    }

    public function testDiff(): void
    {
        $className = \stdClass::class;
        $id = 1;
        $oldRevision = 1;
        $newRevision = 2;

        $this->simpleThingsAuditReader
            ->expects(static::once())
            ->method('diff')
            ->with($className, $id, $oldRevision, $newRevision)
            ->willReturn([]);

        $this->auditReader->diff($className, $id, $oldRevision, $newRevision);
    }

    public function testDiffWithException(): void
    {
        $className = \stdClass::class;
        $id = 1;
        $oldRevision = 1;
        $newRevision = 2;

        $this->simpleThingsAuditReader
            ->expects(static::once())
            ->method('diff')
            ->with($className, $id, $oldRevision, $newRevision)
            ->willThrowException(new \Exception());

        static::assertSame([], $this->auditReader->diff($className, $id, $oldRevision, $newRevision));
    }
}
