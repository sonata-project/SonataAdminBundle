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

class BaseAdminModelManagerTest extends TestCase
{
    public function testHook(): void
    {
        $securityHandler = $this->getMockForAbstractClass(SecurityHandlerInterface::class);

        $modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
        $modelManager->expects($this->once())->method('create');
        $modelManager->expects($this->once())->method('update');
        $modelManager->expects($this->once())->method('delete');

        $admin = new BaseAdminModelManager_Admin('code', 'class', 'controller');
        $admin->setModelManager($modelManager);
        $admin->setSecurityHandler($securityHandler);

        $t = new \stdClass();

        $admin->update($t);
        $admin->create($t);
        $admin->delete($t);
    }

    public function testObject(): void
    {
        $modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
        $modelManager->expects($this->once())->method('find')->willReturnCallback(static function (string $class, int $id): void {
            if ('class' !== $class) {
                throw new \RuntimeException('Invalid class argument');
            }

            if (10 !== $id) {
                throw new \RuntimeException('Invalid id argument');
            }
        });

        $admin = new BaseAdminModelManager_Admin('code', 'class', 'controller');
        $admin->setModelManager($modelManager);
        $admin->getObject(10);
    }

    public function testCreateQuery(): void
    {
        $query = $this->createMock(ProxyQueryInterface::class);
        $modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
        $modelManager
            ->expects($this->once())
            ->method('createQuery')
            ->with('class')
            ->willReturn($query);

        $admin = new BaseAdminModelManager_Admin('code', 'class', 'controller');
        $admin->setModelManager($modelManager);
        $admin->createQuery();
    }

    public function testId(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager
            ->expects($this->exactly(2))
            ->method('getNormalizedIdentifier')
            ->willReturn('42');

        $admin = new BaseAdminModelManager_Admin('code', 'class', 'controller');
        $admin->setModelManager($modelManager);

        $admin->id('Entity');
        $admin->getNormalizedIdentifier('Entity');
    }
}
