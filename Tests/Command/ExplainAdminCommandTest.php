<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Command;

use Sonata\AdminBundle\Command\ExplainAdminCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Validator\MetadataFactoryInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Symfony\Component\Form\FormBuilder;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Symfony\Component\Validator\Mapping\ElementMetadata;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ExplainAdminCommandTest extends \PHPUnit_Framework_TestCase
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

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

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

        $fieldDescription1 = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription1->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('text'));

        $fieldDescription1->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue('SonataAdminBundle:CRUD:foo_text.html.twig'));

        $fieldDescription2 = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');

        $fieldDescription2->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('datetime'));

        $fieldDescription2->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue('SonataAdminBundle:CRUD:bar_datetime.html.twig'));

        $this->admin->expects($this->any())
            ->method('getListFieldDescriptions')
            ->will($this->returnValue(array('fooTextField'=>$fieldDescription1, 'barDateTimeField'=>$fieldDescription2)));

        $this->admin->expects($this->any())
            ->method('getFilterFieldDescriptions')
            ->will($this->returnValue(array('fooTextField'=>$fieldDescription1, 'barDateTimeField'=>$fieldDescription2)));

        $this->admin->expects($this->any())
            ->method('getFormTheme')
            ->will($this->returnValue(array('FooBundle::bar.html.twig')));

        $this->admin->expects($this->any())
            ->method('getFormFieldDescriptions')
            ->will($this->returnValue(array('fooTextField'=>$fieldDescription1, 'barDateTimeField'=>$fieldDescription2)));

        $this->admin->expects($this->any())
            ->method('isChild')
            ->will($this->returnValue(true));

        // php 5.3 BC
        $adminParent = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $adminParent->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('foo_child'));

        $this->admin->expects($this->any())
            ->method('getParent')
            ->will($this->returnCallback(function() use ($adminParent) {
                return $adminParent;
            }));

        $this->validatorFactory = $this->getMock('Symfony\Component\Validator\MetadataFactoryInterface');

        $validator = $this->getMock('Symfony\Component\Validator\ValidatorInterface');
        $validator->expects($this->any())->method('getMetadataFactory')->will($this->returnValue($this->validatorFactory));

        // php 5.3 BC
        $admin = $this->admin;

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function($id) use ($container, $admin, $validator) {
                switch ($id) {
                    case 'sonata.admin.pool':
                        $pool = new Pool($container, '', '');
                        $pool->setAdminServiceIds(array('acme.admin.foo', 'acme.admin.bar'));

                        return $pool;

                    case 'validator':
                        return $validator;

                    case 'acme.admin.foo':
                        return $admin;
                }

                return null;
            }));

        $command->setContainer($container);

        $this->application->add($command);
    }

    public function testExecute()
    {
        $metadata = $this->getMock('Symfony\Component\Validator\MetadataInterface');

        $this->validatorFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with($this->equalTo('Acme\Entity\Foo'))
            ->will($this->returnValue($metadata));

        $propertyMetadata = $this->getMockForAbstractClass('Symfony\Component\Validator\Mapping\ElementMetadata');
        $propertyMetadata->constraints = array(new NotNull(), new Length(array('min' => 2, 'max' => 50, 'groups' => array('create', 'edit'),)));
        $metadata->properties = array('firstName' => $propertyMetadata);

        $getterMetadata = $this->getMockForAbstractClass('Symfony\Component\Validator\Mapping\ElementMetadata');
        $getterMetadata->constraints = array(new NotNull(), new Email(array('groups' => array('registration', 'edit'),)));
        $metadata->getters = array('email' => $getterMetadata);

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        // @todo Mock of \Traversable is available since Phpunit 3.8. This should be completed after stable release of Phpunit 3.8.
        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103
        // $formBuilder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');
        //
        // $this->admin->expects($this->any())
        //     ->method('getFormBuilder')
        //     ->will($this->returnValue($formBuilder));

        $datagridBuilder = $this->getMock('\Sonata\AdminBundle\Builder\DatagridBuilderInterface');

        $this->admin->expects($this->any())
            ->method('getDatagridBuilder')
            ->will($this->returnValue($datagridBuilder));

        $listBuilder = $this->getMock('Sonata\AdminBundle\Builder\ListBuilderInterface');

        $this->admin->expects($this->any())
            ->method('getListBuilder')
            ->will($this->returnValue($listBuilder));

        $command = $this->application->find('sonata:admin:explain');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName(), 'admin'=>'acme.admin.foo'));

        $this->assertEquals(sprintf(str_replace("\n", PHP_EOL, file_get_contents(__DIR__.'/../Fixtures/Command/explain_admin.txt')), get_class($this->admin), get_class($modelManager), get_class($datagridBuilder), get_class($listBuilder)), $commandTester->getDisplay());
    }

    public function testExecuteEmptyValidator()
    {
        $metadata = $this->getMock('Symfony\Component\Validator\MetadataInterface');

        $this->validatorFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with($this->equalTo('Acme\Entity\Foo'))
            ->will($this->returnValue($metadata));

        $metadata->properties = array();
        $metadata->getters = array();

        $modelManager = $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface');

        $this->admin->expects($this->any())
            ->method('getModelManager')
            ->will($this->returnValue($modelManager));

        // @todo Mock of \Traversable is available since Phpunit 3.8. This should be completed after stable release of Phpunit 3.8.
        // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/103
        // $formBuilder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');
        //
        // $this->admin->expects($this->any())
        //     ->method('getFormBuilder')
        //     ->will($this->returnValue($formBuilder));

        $datagridBuilder = $this->getMock('\Sonata\AdminBundle\Builder\DatagridBuilderInterface');

        $this->admin->expects($this->any())
            ->method('getDatagridBuilder')
            ->will($this->returnValue($datagridBuilder));

        $listBuilder = $this->getMock('Sonata\AdminBundle\Builder\ListBuilderInterface');

        $this->admin->expects($this->any())
            ->method('getListBuilder')
            ->will($this->returnValue($listBuilder));

        $command = $this->application->find('sonata:admin:explain');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName(), 'admin'=>'acme.admin.foo'));

        $this->assertEquals(sprintf(str_replace("\n", PHP_EOL, file_get_contents(__DIR__.'/../Fixtures/Command/explain_admin_empty_validator.txt')), get_class($this->admin), get_class($modelManager), get_class($datagridBuilder), get_class($listBuilder)), $commandTester->getDisplay());
    }

    public function testExecuteNonAdminService()
    {
        try {
            $command = $this->application->find('sonata:admin:explain');
            $commandTester = new CommandTester($command);
            $commandTester->execute(array('command' => $command->getName(), 'admin'=>'nonexistent.service'));

        } catch (\RuntimeException $e) {
            $this->assertEquals('Service "nonexistent.service" is not an admin class', $e->getMessage());

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }
}
