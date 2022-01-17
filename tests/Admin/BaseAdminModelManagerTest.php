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

namespace Sonata\AdminBundle\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;

final class BaseAdminModelManagerTest extends TestCase
{
    public function testHook(): void
    {
        $securityHandler = $this->createMock(SecurityHandlerInterface::class);

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->expects(static::once())->method('create');
        $modelManager->expects(static::once())->method('update');
        $modelManager->expects(static::once())->method('delete');

        $admin = new BaseAdminModelManager_Admin();
        $admin->setModelManager($modelManager);
        $admin->setSecurityHandler($securityHandler);

        $t = new \stdClass();

        $admin->update($t);
        $admin->create($t);
        $admin->delete($t);
    }

    public function testObject(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->expects(static::once())->method('find')->willReturnCallback(static function (string $class, int $id): void {
            if (\stdClass::class !== $class) {
                throw new \RuntimeException('Invalid class argument');
            }

            if (10 !== $id) {
                throw new \RuntimeException('Invalid id argument');
            }
        });

        $admin = new BaseAdminModelManager_Admin();
        $admin->setModelClass(\stdClass::class);
        $admin->setModelManager($modelManager);
        $admin->getObject(10);
    }

    public function testCreateQuery(): void
    {
        $query = $this->createMock(ProxyQueryInterface::class);
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->expects(static::once())
            ->method('createQuery')
            ->with(\stdClass::class)
            ->willReturn($query);

        $admin = new BaseAdminModelManager_Admin();
        $admin->setModelClass(\stdClass::class);
        $admin->setModelManager($modelManager);
        $admin->createQuery();
    }

    public function testId(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->expects(static::exactly(2))
            ->method('getNormalizedIdentifier')
            ->willReturn('42');

        $admin = new BaseAdminModelManager_Admin();
        $admin->setModelManager($modelManager);

        $admin->id(new \stdClass());
        $admin->getNormalizedIdentifier(new \stdClass());
    }
}
