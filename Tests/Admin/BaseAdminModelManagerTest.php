<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;

class BaseAdminModelManager_Admin extends AbstractAdmin
{
}

class BaseAdminModelManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testHook()
    {
        $securityHandler = $this->getMock('Sonata\AdminBundle\Security\Handler\SecurityHandlerInterface');

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
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

    public function testObject()
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->expects($this->once())->method('find')->will($this->returnCallback(function ($class, $id) {
            if ($class != 'class') {
                throw new \RuntimeException('Invalid class argument');
            }

            if ($id != 10) {
                throw new \RuntimeException('Invalid id argument');
            }
        }));

        $admin = new BaseAdminModelManager_Admin('code', 'class', 'controller');
        $admin->setModelManager($modelManager);
        $admin->getObject(10);
    }

    public function testCreateQuery()
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->expects($this->once())->method('createQuery')->will($this->returnCallback(function ($class) {
            if ($class != 'class') {
                throw new \RuntimeException('Invalid class argument');
            }
        }));

        $admin = new BaseAdminModelManager_Admin('code', 'class', 'controller');
        $admin->setModelManager($modelManager);
        $admin->createQuery();
    }

    public function testId()
    {
        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManager->expects($this->exactly(2))->method('getNormalizedIdentifier');

        $admin = new BaseAdminModelManager_Admin('code', 'class', 'controller');
        $admin->setModelManager($modelManager);

        $admin->id('Entity');
        $admin->getNormalizedIdentifier('Entity');
    }
}
