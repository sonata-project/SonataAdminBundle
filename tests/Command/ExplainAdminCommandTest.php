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

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\Command\ExplainAdminCommand;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface;
use Symfony\Component\Validator\Mapping\GenericMetadata;
use Symfony\Component\Validator\Mapping\MetadataInterface;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ExplainAdminCommandTest extends TestCase
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var MetadataFactoryInterface
     */
    private $validatorFactory;

    protected function setUp(): void
    {
        $this->application = new Application();
        $command = new ExplainAdminCommand();

        $container = $this->createMock(ContainerInterface::class);

        $this->admin = $this->createMock(AdminInterface::class);

        $this->admin->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('foo'));

        $this->admin->expects($this->any())
            ->method('getClass')
            ->will($this->returnValue('Acme\Entity\Foo'));

        $this->admin->expects($this->any())
            ->method('getBaseControllerName')
            ->will($this->returnValue('SonataAdminBundle:CRUD'));

        $routeCollection = new RouteCollection('foo', 'fooBar', 'foo-bar', 'SonataAdminBundle:CRUD');
        $routeCollection->add('list');
        $routeCollection->add('edit');

        $this->admin->expects($this->any())
            ->method('getRoutes')
            ->will($this->returnValue($routeCollection));

        $fieldDescription1 = $this->createMock(FieldDescriptionInterface::class);

        $fieldDescription1->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('text'));

        $fieldDescription1->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue('@SonataAdmin/CRUD/foo_text.html.twig'));

        $fieldDescription2 = $this->createMock(FieldDescriptionInterface::class);

        $fieldDescription2->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('datetime'));

        $fieldDescription2->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue('@SonataAdmin/CRUD/bar_datetime.html.twig'));

        $this->admin->expects($this->any())
            ->method('getListFieldDescriptions')
            ->will($this->returnValue([
                'fooTextField' => $fieldDescription1,
                'barDateTimeField' => $fieldDescription2,
            ]));

        $this->admin->expects($this->any())
            ->method('getFilterFieldDescriptions')
            ->will($this->returnValue([
                'fooTextField' => $fieldDescription1,
                'barDateTimeField' => $fieldDescription2,
            ]));

        $this->admin->expects($this->any())
            ->method('getFormTheme')
            ->will($this->returnValue(['@Foo/bar.html.twig']));

        $this->admin->expects($this->any())
            ->method('getFormFieldDescriptions')
            ->will($this->returnValue([
                'fooTextField' => $fieldDescription1,
                'barDateTimeField' => $fieldDescription2,
            ]));

        $this->admin->expects($this->any())
            ->method('isChild')
            ->will($this->returnValue(true));

        $this->admin->expects($this->any())
            ->method('getParent')
            ->will($this->returnCallback(function () {
                $adminParent = $this->createMock(AdminInterface::class);

                $adminParent->expects($this->any())
                    ->method('getCode')
                    ->will($this->returnValue('foo_child'));

                return $adminParent;
            }));

        $this->validatorFactory = $this->createMock(MetadataFactoryInterface::class);

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use ($container) {
                switch ($id) {
                    case 'sonata.admin.pool':
                        $pool = new Pool($container, '', '');
                        $pool->setAdminServiceIds(['acme.admin.foo', 'acme.admin.bar']);

                        return $pool;

                    case 'validator':
                        return $this->validatorFactory;

                    case 'acme.admin.foo':
                        return $this->admin;
                }
            }));

        $container->expects($this->any())->method('has')->will($this->returnValue(true));

        $command->setContainer($container);

        $this->application->add($command);
    }

    public function testExecute(): void
    {
        $metadata = $this->createMock(MetadataInterface::class);

        $this->validatorFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with($this->equalTo('Acme\Entity\Foo'))
            ->will($this->returnValue($metadata));

        $propertyMetadata = $this->getMockForAbstractClass(GenericMetadata::class);
        $propertyMetadata->constraints = [
            new NotNull(),
            new Length(['min' => 2, 'max' => 50, 'groups' => ['create', 'edit']]),
        ];

        $metadata->properties = ['firstName' => $propertyMetadata];

        $getterMetadata = $this->getMockForAbstractClass(GenericMetadata::class);
        $getterMetadata->constraints = [
            new NotNull(),
            new Email(['groups' => ['registration', 'edit']]),
        ];

        $metadata->getters = ['email' => $getterMetadata];

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $formBuilder = $this->createMock(FormBuilderInterface::class);

        $this->admin->expects($this->any())
             ->method('getFormBuilder')
             ->will($this->returnValue($formBuilder));

        $datagridBuilder = $this->createMock(DatagridBuilderInterface::class);

        $this->admin->expects($this->any())
            ->method('getDatagridBuilder')
            ->will($this->returnValue($datagridBuilder));

        $listBuilder = $this->createMock(ListBuilderInterface::class);

        $this->admin->expects($this->any())
            ->method('getListBuilder')
            ->will($this->returnValue($listBuilder));

        $command = $this->application->find('sonata:admin:explain');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), 'admin' => 'acme.admin.foo']);

        $this->assertSame(sprintf(
            str_replace("\n", PHP_EOL, file_get_contents(__DIR__.'/../Fixtures/Command/explain_admin.txt')),
            \get_class($this->admin),
            \get_class($modelManager),
            \get_class($formBuilder),
            \get_class($datagridBuilder),
            \get_class($listBuilder)
        ), $commandTester->getDisplay());
    }

    public function testExecuteEmptyValidator(): void
    {
        $metadata = $this->createMock(MetadataInterface::class);

        $this->validatorFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with($this->equalTo('Acme\Entity\Foo'))
            ->will($this->returnValue($metadata));

        $metadata->properties = [];
        $metadata->getters = [];

        $modelManager = $this->createMock(ModelManagerInterface::class);

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $formBuilder = $this->createMock(FormBuilderInterface::class);

        $this->admin->expects($this->any())
             ->method('getFormBuilder')
             ->will($this->returnValue($formBuilder));

        $datagridBuilder = $this->createMock(DatagridBuilderInterface::class);

        $this->admin->expects($this->any())
            ->method('getDatagridBuilder')
            ->will($this->returnValue($datagridBuilder));

        $listBuilder = $this->createMock(ListBuilderInterface::class);

        $this->admin->expects($this->any())
            ->method('getListBuilder')
            ->will($this->returnValue($listBuilder));

        $command = $this->application->find('sonata:admin:explain');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), 'admin' => 'acme.admin.foo']);

        $this->assertSame(sprintf(
            str_replace(
                "\n",
                PHP_EOL,
                file_get_contents(__DIR__.'/../Fixtures/Command/explain_admin_empty_validator.txt')
            ),
            \get_class($this->admin),
            \get_class($modelManager),
            \get_class($formBuilder),
            \get_class($datagridBuilder),
            \get_class($listBuilder)
        ), $commandTester->getDisplay());
    }

    public function testExecuteNonAdminService(): void
    {
        try {
            $command = $this->application->find('sonata:admin:explain');
            $commandTester = new CommandTester($command);
            $commandTester->execute(['command' => $command->getName(), 'admin' => 'nonexistent.service']);
        } catch (\RuntimeException $e) {
            $this->assertSame('Service "nonexistent.service" is not an admin class', $e->getMessage());

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }
}
