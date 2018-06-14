<?php

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
use Sonata\AdminBundle\DependencyInjection\Compiler\AdminMakerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Prophecy\Argument;

/**
 * @author Gaurav Singh Faujdar <faujdar@gmail.com>
 */
class AdminMakerCompilerPassTest extends TestCase
{
    private $adminMaker;

    public function setUp()
    {
        parent::setUp();
        $this->adminMaker = $this->prophesize(Definition::class);
        $this->adminMaker->setArgument(Argument::type('integer'), Argument::any())->shouldBeCalledTimes(2);
    }

    public function testProcess()
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


        $compilerPass = new AdminMakerCompilerPass();
        $compilerPass->process($containerBuilderMock->reveal());

        $this->verifyMockObjects();
    }
}
