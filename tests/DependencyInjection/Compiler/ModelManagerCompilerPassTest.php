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

namespace Sonata\AdminBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sonata\AdminBundle\DependencyInjection\Compiler\ModelManagerCompilerPass;
use Sonata\AdminBundle\Maker\AdminMaker;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Gaurav Singh Faujdar <faujdar@gmail.com>
 */
class ModelManagerCompilerPassTest extends TestCase
{
    /**
     * @var AdminMaker
     */
    private $adminMaker;

    public function setUp(): void
    {
        if (!class_exists('Symfony\Component\Console\CommandLoader\CommandLoaderInterface')) {
            $this->markTestSkipped('Test only available for SF 3.4');
        }

        parent::setUp();
        $this->adminMaker = $this->prophesize(Definition::class);
        $this->adminMaker->replaceArgument(Argument::type('integer'), Argument::any())->shouldBeCalledTimes(2);
    }

    public function testProcess(): void
    {
        $containerBuilderMock = $this->prophesize(ContainerBuilder::class);

        $containerBuilderMock->getDefinition(Argument::exact('sonata.admin.maker'))
            ->willReturn($this->adminMaker);

        $containerBuilderMock->hasDefinition(Argument::containingString('sonata.admin.manager'))
            ->willReturn(null);
        $containerBuilderMock->getDefinition(Argument::containingString('sonata.admin.manager'))
            ->willReturn(null);
        $containerBuilderMock->getParameter(Argument::containingString('kernel.project_dir'))
            ->willReturn(null);

        $compilerPass = new ModelManagerCompilerPass();
        $compilerPass->process($containerBuilderMock->reveal());

        $this->verifyMockObjects();
    }
}
