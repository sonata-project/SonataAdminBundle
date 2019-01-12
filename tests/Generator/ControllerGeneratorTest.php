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
use Sonata\AdminBundle\Generator\ControllerGenerator;
use Sonata\AdminBundle\Tests\Fixtures\Controller\ModelAdminController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * @author Marek Stipek <mario.dweller@seznam.cz>
 */
class ControllerGeneratorTest extends TestCase
{
    /** @var ControllerGenerator */
    private $controllerGenerator;

    /** @var BundleInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $bundleMock;

    /** @var string */
    private $bundlePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->controllerGenerator = new ControllerGenerator(__DIR__.'/../../src/Resources/skeleton');
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
        $this->controllerGenerator->generate($this->bundleMock, 'ModelAdminController');
        $file = $this->controllerGenerator->getFile();
        $this->assertSame(
            ModelAdminController::class,
            $this->controllerGenerator->getClass()
        );
        $this->assertSame('ModelAdminController.php', basename($file));
        $this->assertFileEquals(__DIR__.'/../Fixtures/Controller/ModelAdminController.php', $file);

        $this->expectException(\RuntimeException::class, 'already exists');

        $this->controllerGenerator->generate($this->bundleMock, 'ModelAdminController');
    }

    private function createBundleMock(): BundleInterface
    {
        $bundleMock = $this->getMockForAbstractClass(BundleInterface::class);
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
