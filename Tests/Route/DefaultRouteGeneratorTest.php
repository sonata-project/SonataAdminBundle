<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Route;

use Sonata\AdminBundle\Route\DefaultRouteGenerator;

class DefaultRouteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $router = $this->getMock('\Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())->method('generate')->will($this->returnValue('/foo/bar'));

        $generator = new DefaultRouteGenerator($router);

        $this->assertEquals('/foo/bar', $generator->generate('foo_bar'));
    }
}
