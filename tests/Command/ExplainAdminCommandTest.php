<?php

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
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Command\ExplainAdminCommand;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;

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
     * @var Symfony\Component\Validator\MetadataFactoryInterface
     */
    private $validatorFactory;

    protected function setUp()
    {
        $this->application = new Application();
        $command = new ExplainAdminCommand();

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');

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

        $fieldDescription1 = $this->createMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription1->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('text'));

        $fieldDescription1->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue('SonataAdminBundle:CRUD:foo_text.html.twig'));

        $fieldDescription2 = $this->createMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription2->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('datetime'));

        $fieldDescription2->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue('SonataAdminBundle:CRUD:bar_datetime.html.twig'));

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
            ->will($this->returnValue(['FooBundle::bar.html.twig']));

        $this->admin->expects($this->any())
            ->method('getFormFieldDescriptions')
            ->will($this->returnValue([
                'fooTextField' => $fieldDescription1,
                'barDateTimeField' => $fieldDescription2,
            ]));

        $this->admin->expects($this->any())
            ->method('isChild')
            ->will($this->returnValue(true));

        // php 5.3 BC
        $adminParent = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $adminParent->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('foo_child'));

        $this->admin->expects($this->any())
            ->method('getParent')
            ->will($this->returnCallback(function () use ($adminParent) {
                return $adminParent;
            }));

        $this->validatorFactory = $this->createMock('Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface');

        $validator = $this->createMock('Symfony\Component\Validator\Validator\ValidatorInterface');
        $validator->expects($this->any())->method('getMetadataFor')->will(
            $this->returnValue($this->validatorFactory)
        );

        // php 5.3 BC
        $admin = $this->admin;
        $validatorFactory = $this->validatorFactory;

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use ($container, $admin, $validator, $validatorFactory) {
                switch ($id) {
                    case 'sonata.admin.pool':
                        $pool = new Pool($container, '', '');
                        $pool->setAdminServiceIds(['acme.admin.foo', 'acme.admin.bar']);

                        return $pool;

                    case 'validator.validator_factory':
                        return $validatorFactory;

                    case 'validator':
                        return $validator;

                    case 'acme.admin.foo':
                        return $admin;
                }

                return;
            }));

        $container->expects($this->any())->method('has')->will($this->returnValue(true));

        $command->setContainer($container);

        $this->application->add($command);
    }

    public function testExecute()
    {
        $metadata = $this->createMock('Symfony\Component\Validator\Mapping\MetadataInterface');

        $this->validatorFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with($this->equalTo('Acme\Entity\Foo'))
            ->will($this->returnValue($metadata));

        $propertyMetadata = $this->getMockForAbstractClass('Symfony\Component\Validator\Mapping\GenericMetadata');
        $propertyMetadata->constraints = [
            new NotNull(),
            new Length(['min' => 2, 'max' => 50, 'groups' => ['create', 'edit']]),
        ];

        $metadata->properties = ['firstName' => $propertyMetadata];

        $getterMetadata = $this->getMockForAbstractClass('Symfony\Component\Validator\Mapping\GenericMetadata');
        $getterMetadata->constraints = [
            new NotNull(),
            new Email(['groups' => ['registration', 'edit']]),
        ];

        $metadata->getters = ['email' => $getterMetadata];

        $modelManager = $this->createMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $formBuilder = $this->createMock('Symfony\Component\Form\FormBuilderInterface');

        $this->admin->expects($this->any())
             ->method('getFormBuilder')
             ->will($this->returnValue($formBuilder));

        $datagridBuilder = $this->createMock('\Sonata\AdminBundle\Builder\DatagridBuilderInterface');

        $this->admin->expects($this->any())
            ->method('getDatagridBuilder')
            ->will($this->returnValue($datagridBuilder));

        $listBuilder = $this->createMock('Sonata\AdminBundle\Builder\ListBuilderInterface');

        $this->admin->expects($this->any())
            ->method('getListBuilder')
            ->will($this->returnValue($listBuilder));

        $command = $this->application->find('sonata:admin:explain');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), 'admin' => 'acme.admin.foo']);

        $this->assertSame(sprintf(
            str_replace("\n", PHP_EOL, file_get_contents(__DIR__.'/../Fixtures/Command/explain_admin.txt')),
            get_class($this->admin),
            get_class($modelManager),
            get_class($formBuilder),
            get_class($datagridBuilder),
            get_class($listBuilder)
        ), $commandTester->getDisplay());
    }

    public function testExecuteEmptyValidator()
    {
        if (interface_exists('Symfony\Component\Validator\Mapping\MetadataInterface')) { //sf2.5+
            $metadata = $this->createMock('Symfony\Component\Validator\Mapping\MetadataInterface');
        } else {
            $metadata = $this->createMock('Symfony\Component\Validator\MetadataInterface');
        }

        $this->validatorFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with($this->equalTo('Acme\Entity\Foo'))
            ->will($this->returnValue($metadata));

        $metadata->properties = [];
        $metadata->getters = [];

        $modelManager = $this->createMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        $formBuilder = $this->createMock('Symfony\Component\Form\FormBuilderInterface');

        $this->admin->expects($this->any())
             ->method('getFormBuilder')
             ->will($this->returnValue($formBuilder));

        $datagridBuilder = $this->createMock('\Sonata\AdminBundle\Builder\DatagridBuilderInterface');

        $this->admin->expects($this->any())
            ->method('getDatagridBuilder')
            ->will($this->returnValue($datagridBuilder));

        $listBuilder = $this->createMock('Sonata\AdminBundle\Builder\ListBuilderInterface');

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
            get_class($this->admin),
            get_class($modelManager),
            get_class($formBuilder),
            get_class($datagridBuilder),
            get_class($listBuilder)
        ), $commandTester->getDisplay());
    }

    public function testExecuteNonAdminService()
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
