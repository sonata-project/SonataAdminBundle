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

namespace Sonata\AdminBundle\Tests\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Command\ExplainAdminCommand;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
final class ExplainAdminCommandTest extends TestCase
{
    private Application $application;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private AdminInterface $admin;

    protected function setUp(): void
    {
        $this->application = new Application();

        $container = new Container();

        $this->admin = $this->createMock(AdminInterface::class);

        $this->admin
            ->method('getCode')
            ->willReturn('foo');

        $this->admin
            ->method('getClass')
            ->willReturn('Acme\Entity\Foo');

        $this->admin
            ->method('getBaseControllerName')
            ->willReturn(CRUDController::class);

        $routeCollection = new RouteCollection('foo', 'fooBar', 'foo-bar', CRUDController::class);
        $routeCollection->add('list');
        $routeCollection->add('edit');

        $this->admin
            ->method('getRoutes')
            ->willReturn($routeCollection);

        $fieldDescription1 = $this->createMock(FieldDescriptionInterface::class);

        $fieldDescription1
            ->method('getType')
            ->willReturn('text');

        $fieldDescription1
            ->method('getTemplate')
            ->willReturn('@SonataAdmin/CRUD/foo_text.html.twig');

        $fieldDescription2 = $this->createMock(FieldDescriptionInterface::class);

        $fieldDescription2
            ->method('getType')
            ->willReturn('datetime');

        $fieldDescription2
            ->method('getTemplate')
            ->willReturn('@SonataAdmin/CRUD/bar_datetime.html.twig');

        $this->admin
            ->method('getListFieldDescriptions')
            ->willReturn([
                'fooTextField' => $fieldDescription1,
                'barDateTimeField' => $fieldDescription2,
            ]);

        $this->admin
            ->method('getFilterFieldDescriptions')
            ->willReturn([
                'fooTextField' => $fieldDescription1,
                'barDateTimeField' => $fieldDescription2,
            ]);

        $this->admin
            ->method('getFormTheme')
            ->willReturn(['@Foo/bar.html.twig']);

        $this->admin
            ->method('getFormFieldDescriptions')
            ->willReturn([
                'fooTextField' => $fieldDescription1,
                'barDateTimeField' => $fieldDescription2,
            ]);

        $this->admin
            ->method('isChild')
            ->willReturn(true);

        $this->admin
            ->method('getParent')
            ->willReturnCallback(function () {
                $adminParent = $this->createMock(AdminInterface::class);

                $adminParent
                    ->method('getCode')
                    ->willReturn('foo_child');

                return $adminParent;
            });

        $container->set('acme.admin.foo', $this->admin);

        $pool = new Pool($container, ['acme.admin.foo', 'acme.admin.bar']);

        $command = new ExplainAdminCommand($pool);

        $this->application->add($command);
    }

    public function testExecute(): void
    {
        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $formBuilder = $this->createMock(FormBuilderInterface::class);

        $this->admin
             ->method('getFormBuilder')
             ->willReturn($formBuilder);

        $datagridBuilder = $this->createMock(DatagridBuilderInterface::class);

        $this->admin
            ->method('getDatagridBuilder')
            ->willReturn($datagridBuilder);

        $listBuilder = $this->createMock(ListBuilderInterface::class);

        $this->admin
            ->method('getListBuilder')
            ->willReturn($listBuilder);

        $command = $this->application->find('sonata:admin:explain');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), 'admin' => 'acme.admin.foo']);

        $explainAdminText = file_get_contents(\sprintf('%s/../Fixtures/Command/explain_admin.txt', __DIR__));
        static::assertNotFalse($explainAdminText);

        static::assertSame(\sprintf(
            str_replace("\n", \PHP_EOL, $explainAdminText),
            $this->admin::class,
            $modelManager::class,
            $formBuilder::class,
            $datagridBuilder::class,
            $listBuilder::class
        ), $commandTester->getDisplay());
    }

    public function testExecuteNonAdminService(): void
    {
        $command = $this->application->find('sonata:admin:explain');
        $commandTester = new CommandTester($command);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Admin service "nonexistent.service" not found in admin pool. Did you mean "acme.admin.bar" or one of those: []');

        $commandTester->execute(['command' => $command->getName(), 'admin' => 'nonexistent.service']);
    }
}
