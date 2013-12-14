<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Generator;

use Sonata\AdminBundle\Generator\AbstractBcGenerator;
use Sonata\AdminBundle\Tests\Fixtures\Generator\GeneratorBc22;
use Sonata\AdminBundle\Tests\Fixtures\Generator\GeneratorBc23;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class AbstractBcGeneratorTest extends \PHPUnit_Framework_TestCase
{
    private static $errorReportingBackup;

    public static function setUpBeforeClass()
    {
        // Disable E_STRICT errors for this test only
        self::$errorReportingBackup = error_reporting();
        error_reporting(self::$errorReportingBackup ^ E_STRICT);
    }

    public static function tearDownAfterClass()
    {
        // Restore error reporting
        error_reporting(self::$errorReportingBackup);
    }

    public function testRenderBc22()
    {
        if (AbstractBcGenerator::getGeneratorVersion() != '2.2') {
            $this->markTestSkipped('Wrong Sensio Generator version, expected 2.2');
        }

        $skeletonDir = 'path/to/templates';
        $template = 'test.html.twig';
        $parameters = array('foo' => 'bar');

        $generator22 = new GeneratorBc22();
        $generator22->setSkeletonDirs($skeletonDir);
        $generator22->setBc(true);
        $this->assertEquals('Result OK', $this->callMethod($generator22, 'renderBc', array($template, $parameters)));
    }

    public function testRenderFileBc22()
    {
        if (AbstractBcGenerator::getGeneratorVersion() != '2.2') {
            $this->markTestSkipped('Wrong Sensio Generator version, expected 2.2');
        }

        $skeletonDir = 'path/to/templates';
        $template = 'test.html.twig';
        $parameters = array('foo' => 'bar');
        $target = 'target_file';

        $generator22 = new GeneratorBc22();
        $generator22->setSkeletonDirs($skeletonDir);
        $generator22->setBc(true);
        $this->assertTrue($this->callMethod($generator22, 'renderFileBc', array($template, $target, $parameters)));
    }

    public function testRenderBc23()
    {
        if (AbstractBcGenerator::getGeneratorVersion() != '2.3') {
            $this->markTestSkipped('Wrong Sensio Generator version, expected 2.3');
        }

        $skeletonDir = 'path/to/templates';
        $template = 'test.html.twig';
        $parameters = array('foo' => 'bar');

        $generator23 = new GeneratorBc23();
        $generator23->setSkeletonDirs($skeletonDir);
        $generator23->setBc(false);
        $this->assertEquals('Result OK', $this->callMethod($generator23, 'renderBc', array($template, $parameters)));
    }

    public function testRenderFileBc23()
    {
        if (AbstractBcGenerator::getGeneratorVersion() != '2.3') {
            $this->markTestSkipped('Wrong Sensio Generator version, expected 2.3');
        }

        $skeletonDir = 'path/to/templates';
        $template = 'test.html.twig';
        $parameters = array('foo' => 'bar');
        $target = 'target_file';

        $generator23 = new GeneratorBc23();
        $generator23->setSkeletonDirs($skeletonDir);
        $generator23->setBc(false);
        $this->assertTrue($this->callMethod($generator23, 'renderFileBc', array($template, $target, $parameters)));
    }


    protected function callMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
