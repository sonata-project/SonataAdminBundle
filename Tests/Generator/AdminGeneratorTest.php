<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Generator;

use Sonata\AdminBundle\Generator\AdminGenerator;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * @author Marek Stipek <mario.dweller@seznam.cz>
 */
class AdminGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var AdminGenerator */
    private $adminGenerator;

    /** @var BundleInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $bundleMock;

    /** @var string */
    private $bundlePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->adminGenerator = new AdminGenerator(
            $this->createModelManagerMock(),
            __DIR__.'/../../Resources/skeleton'
        );
        $this->bundleMock = $this->createBundleMock();
        $this->bundlePath = $this->bundleMock->getPath();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->bundlePath);
    }

    public function testGenerate()
    {
        $this->adminGenerator->generate($this->bundleMock, 'ModelAdmin', 'Model');
        $file = $this->adminGenerator->getFile();
        $this->assertSame('Sonata\AdminBundle\Tests\Fixtures\Admin\ModelAdmin', $this->adminGenerator->getClass());
        $this->assertSame('ModelAdmin.php', basename($file));
        $this->assertFileEquals(__DIR__.'/../Fixtures/Admin/ModelAdmin.php', $file);

        try {
            $this->adminGenerator->generate($this->bundleMock, 'ModelAdmin', 'Model');
        } catch (\RuntimeException $e) {
            $this->assertContains('already exists', $e->getMessage());

            return;
        }

        $this->fail('Failed asserting that exception of type "\RuntimeException" is thrown.');
    }

    /**
     * @return ModelManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createModelManagerMock()
    {
        $modelManagerMock = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelManagerMock
            ->expects($this->any())
            ->method('getExportFields')
            ->with('Model')
            ->will($this->returnValue(array('foo', 'bar', 'baz')))
        ;

        return $modelManagerMock;
    }

    /**
     * @return BundleInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createBundleMock()
    {
        $bundleMock = $this->getMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $bundleMock
            ->expects($this->any())
            ->method('getNamespace')
            ->will($this->returnValue('Sonata\AdminBundle\Tests\Fixtures'))
        ;
        $bundleMock
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue(sprintf('%s/%s', sys_get_temp_dir(), lcg_value())))
        ;

        return $bundleMock;
    }
}
