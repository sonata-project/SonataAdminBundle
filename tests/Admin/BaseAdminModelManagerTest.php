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
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Security\Acl\Permission\MaskBuilder;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandler;
use Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class BaseAdminModelManagerTest extends TestCase
{
    public function testHook(): void
    {
        $securityHandler = static::createStub(SecurityHandlerInterface::class);

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

    public function testDeleteRemovesTheAclAfterTheObject(): void
    {
        $object = new class {
            public ?int $id = 42;

            public function getId(): ?int
            {
                return $this->id;
            }
        };

        $calls = [];

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->expects(static::once())->method('delete')->with($object)
            ->willReturnCallback(static function () use ($object, &$calls): void {
                $calls[] = 'delete';
                // Like Doctrine does for generated identifiers.
                $object->id = null;
            });

        $aclProvider = $this->createMock(MutableAclProviderInterface::class);
        $aclProvider->expects(static::once())->method('deleteAcl')
            ->willReturnCallback(static function (ObjectIdentityInterface $objectIdentity) use (&$calls): void {
                static::assertSame('42', $objectIdentity->getIdentifier());
                $calls[] = 'deleteAcl';
            });

        $admin = new BaseAdminModelManager_Admin();
        $admin->setModelManager($modelManager);
        $admin->setSecurityHandler($this->createAclSecurityHandler($aclProvider));

        $admin->delete($object);

        static::assertSame(['delete', 'deleteAcl'], $calls);
    }

    public function testDeleteKeepsTheAclWhenTheObjectCannotBeDeleted(): void
    {
        $object = new class {
            public function getId(): int
            {
                return 42;
            }
        };

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->method('delete')->willThrowException(new ModelManagerException('Foreign key violation'));

        $aclProvider = $this->createMock(MutableAclProviderInterface::class);
        $aclProvider->expects(static::never())->method('deleteAcl');

        $admin = new BaseAdminModelManager_Admin();
        $admin->setModelManager($modelManager);
        $admin->setSecurityHandler($this->createAclSecurityHandler($aclProvider));

        $this->expectException(ModelManagerException::class);

        $admin->delete($object);
    }

    public function testDeleteCallsOtherSecurityHandlersBeforeTheObjectIsDeleted(): void
    {
        $object = new \stdClass();
        $calls = [];

        $securityHandler = $this->createMock(SecurityHandlerInterface::class);
        $securityHandler->expects(static::once())->method('deleteObjectSecurity')
            ->willReturnCallback(static function () use (&$calls): void {
                $calls[] = 'deleteObjectSecurity';
            });

        $modelManager = $this->createMock(ModelManagerInterface::class);
        $modelManager->expects(static::once())->method('delete')
            ->willReturnCallback(static function () use (&$calls): void {
                $calls[] = 'delete';
            });

        $admin = new BaseAdminModelManager_Admin();
        $admin->setModelManager($modelManager);
        $admin->setSecurityHandler($securityHandler);

        $admin->delete($object);

        static::assertSame(['deleteObjectSecurity', 'delete'], $calls);
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
        $query = static::createStub(ProxyQueryInterface::class);
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

    private function createAclSecurityHandler(MutableAclProviderInterface $aclProvider): AclSecurityHandler
    {
        return new AclSecurityHandler(
            static::createStub(TokenStorageInterface::class),
            static::createStub(AuthorizationCheckerInterface::class),
            $aclProvider,
            MaskBuilder::class,
            'ROLE_SUPER_ADMIN'
        );
    }
}
