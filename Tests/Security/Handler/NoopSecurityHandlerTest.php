<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\AdminBundle\Tests\Admin\Security\Acl\Permission;

use Sonata\AdminBundle\Security\Handler\NoopSecurityHandler;

class NoopSecurityHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testNoop()
    {
        $handler = new NoopSecurityHandler;

        $this->assertTrue($handler->isGranted(array('TOTO')));
        $this->assertTrue($handler->isGranted('TOTO'));
    }
}