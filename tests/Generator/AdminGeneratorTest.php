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

namespace Sonata\AdminBundle\Tests\Generator;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Generator\AdminGenerator;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Tests\Fixtures\Admin\ModelAdmin;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * @author Marek Stipek <mario.dweller@seznam.cz>
 *
 * @group legacy
 */
class AdminGeneratorTest extends TestCase
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
    protected function setUp(): void
    {
        $this->adminGenerator = new AdminGenerator(
            $this->createModelManagerMock(),
            __DIR__.'/../../src/Resources/skeleton'
        );
        $this->bundleMock = $this->createBundleMock();
        $this->bundlePath = $this->bundleMock->getPath();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->bundlePath);
    }

    public function testGenerate(): void
    {
        $this->adminGenerator->generate($this->bundleMock, 'ModelAdmin', 'Model');
        $file = $this->adminGenerator->getFile();
        $this->assertSame(ModelAdmin::class, $this->adminGenerator->getClass());
        $this->assertSame('ModelAdmin.php', basename($file));
        $this->assertFileEquals(__DIR__.'/../Fixtures/Admin/ModelAdmin.php', $file);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('already exists');

        $this->adminGenerator->generate($this->bundleMock, 'ModelAdmin', 'Model');
    }

    private function createModelManagerMock(): ModelManagerInterface
    {
        $modelManagerMock = $this->getMockForAbstractClass(ModelManagerInterface::class);
        $modelManagerMock
            ->expects($this->any())
            ->method('getExportFields')
            ->with('Model')
            ->willReturn(['foo', 'bar', 'baz'])
        ;

        return $modelManagerMock;
    }

    private function createBundleMock(): BundleInterface
    {
        $bundleMock = $this->getMockForAbstractClass(BundleInterface::class);
        $bundleMock
            ->expects($this->any())
            ->method('getNamespace')
            ->willReturn('Sonata\AdminBundle\Tests\Fixtures')
        ;
        $bundleMock
            ->expects($this->any())
            ->method('getPath')
            ->willReturn(sprintf('%s/%s', sys_get_temp_dir(), lcg_value()))
        ;

        return $bundleMock;
    }
}
