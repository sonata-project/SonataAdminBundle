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
use Sonata\AdminBundle\Command\GenerateAdminCommand;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\DemoAdminBundle;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class GenerateAdminCommandTest extends TestCase
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var string
     */
    private $tempDirectory;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var GenerateAdminCommand
     */
    private $command;

    protected function setUp()
    {
        // create temp dir
        $tempfile = tempnam(sys_get_temp_dir(), 'sonata_admin');
        if (file_exists($tempfile)) {
            unlink($tempfile);
        }
        mkdir($tempfile);
        $this->tempDirectory = $tempfile;

        $bundle = new DemoAdminBundle();
        $bundle->setPath($this->tempDirectory);

        $kernel = $this->createMock('Symfony\Component\HttpKernel\KernelInterface');
        $kernel->expects($this->any())
            ->method('getBundles')
            ->will($this->returnValue([$bundle]));

        $parameterBag = new ParameterBag();
        $this->container = new Container($parameterBag);

        $kernel->expects($this->any())
            ->method('getBundle')
            ->with($this->equalTo('DemoAdminBundle'))
            ->will($this->returnValue($bundle));

        $kernel->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue($this->container));

        $kernel->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue($this->tempDirectory));

        $this->application = new Application($kernel);
        $this->command = new GenerateAdminCommand();

        $this->application->add($this->command);
    }

    public function tearDown()
    {
        if ($this->tempDirectory) {
            if (file_exists($this->tempDirectory.'/Controller/FooAdminController.php')) {
                unlink($this->tempDirectory.'/Controller/FooAdminController.php');
            }

            if (file_exists($this->tempDirectory.'/Admin/FooAdmin.php')) {
                unlink($this->tempDirectory.'/Admin/FooAdmin.php');
            }

            if (file_exists($this->tempDirectory.'/Resources/config/admin.yml')) {
                unlink($this->tempDirectory.'/Resources/config/admin.yml');
            }

            if (file_exists($this->tempDirectory.'/Resources/config/services.xml')) {
                unlink($this->tempDirectory.'/Resources/config/services.xml');
            }

            if (file_exists($this->tempDirectory.'/config/services.xml')) {
                unlink($this->tempDirectory.'/config/services.xml');
            }

            if (is_dir($this->tempDirectory.'/Controller')) {
                rmdir($this->tempDirectory.'/Controller');
            }

            if (is_dir($this->tempDirectory.'/Admin')) {
                rmdir($this->tempDirectory.'/Admin');
            }

            if (is_dir($this->tempDirectory.'/Resources/config')) {
                rmdir($this->tempDirectory.'/Resources/config');
            }

            if (is_dir($this->tempDirectory.'/Resources')) {
                rmdir($this->tempDirectory.'/Resources');
            }

            if (is_dir($this->tempDirectory.'/config')) {
                rmdir($this->tempDirectory.'/config');
            }

            if (file_exists($this->tempDirectory) && is_dir($this->tempDirectory)) {
                rmdir($this->tempDirectory);
            }
        }
    }

    public function testExecute()
    {
        $this->command->setContainer($this->container);
        $this->container->set('sonata.admin.manager.foo', $this->createMock('Sonata\AdminBundle\Model\ModelManagerInterface'));

        $command = $this->application->find('sonata:admin:generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'model' => 'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo',
            '--bundle' => 'DemoAdminBundle',
            '--admin' => 'FooAdmin',
            '--controller' => 'FooAdminController',
            '--services' => 'admin.yml',
            '--id' => 'acme_demo_admin.admin.foo',
        ], ['interactive' => false]);

        $expectedOutput = '';
        $expectedOutput .= sprintf('%3$sThe admin class "Sonata\AdminBundle\Tests\Fixtures\Bundle\Admin\FooAdmin" has been generated under the file "%1$s%2$sAdmin%2$sFooAdmin.php".%3$s', $this->tempDirectory, DIRECTORY_SEPARATOR, PHP_EOL);
        $expectedOutput .= sprintf('%3$sThe controller class "Sonata\AdminBundle\Tests\Fixtures\Bundle\Controller\FooAdminController" has been generated under the file "%1$s%2$sController%2$sFooAdminController.php".%3$s', $this->tempDirectory, DIRECTORY_SEPARATOR, PHP_EOL);
        $expectedOutput .= sprintf('%3$sThe service "acme_demo_admin.admin.foo" has been appended to the file "%1$s%2$sResources%2$sconfig%2$sadmin.yml".%3$s', $this->tempDirectory, DIRECTORY_SEPARATOR, PHP_EOL);

        $this->assertSame($expectedOutput, $commandTester->getDisplay());

        $this->assertFileExists($this->tempDirectory.'/Admin/FooAdmin.php');
        $this->assertFileExists($this->tempDirectory.'/Controller/FooAdminController.php');
        $this->assertFileExists($this->tempDirectory.'/Resources/config/admin.yml');

        $adminContent = file_get_contents($this->tempDirectory.'/Admin/FooAdmin.php');
        $this->assertContains('class FooAdmin extends AbstractAdmin', $adminContent);
        $this->assertContains('use Sonata\AdminBundle\Admin\AbstractAdmin;', $adminContent);
        $this->assertContains('use Sonata\AdminBundle\Datagrid\DatagridMapper;', $adminContent);
        $this->assertContains('use Sonata\AdminBundle\Datagrid\ListMapper;', $adminContent);
        $this->assertContains('use Sonata\AdminBundle\Form\FormMapper;', $adminContent);
        $this->assertContains('use Sonata\AdminBundle\Show\ShowMapper;', $adminContent);
        $this->assertContains('protected function configureDatagridFilters(DatagridMapper $datagridMapper)', $adminContent);
        $this->assertContains('protected function configureListFields(ListMapper $listMapper)', $adminContent);
        $this->assertContains('protected function configureFormFields(FormMapper $formMapper)', $adminContent);
        $this->assertContains('protected function configureShowFields(ShowMapper $showMapper)', $adminContent);

        $controllerContent = file_get_contents($this->tempDirectory.'/Controller/FooAdminController.php');
        $this->assertContains('class FooAdminController extends CRUDController', $controllerContent);
        $this->assertContains('use Sonata\AdminBundle\Controller\CRUDController;', $controllerContent);

        $configServiceContent = file_get_contents($this->tempDirectory.'/Resources/config/admin.yml');
        $this->assertContains('services:'."\n".'    acme_demo_admin.admin.foo', $configServiceContent);
        $this->assertContains('            - { name: sonata.admin, manager_type: foo, group: admin, label: Foo }', $configServiceContent);
    }

    public function testExecuteWithExceptionNoModelManagers()
    {
        $this->expectException('RuntimeException', 'There are no model managers registered.');

        $this->command->setContainer($this->container);

        $command = $this->application->find('sonata:admin:generate');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'model' => 'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo',
            '--bundle' => 'DemoAdminBundle',
            '--admin' => 'FooAdmin',
            '--controller' => 'FooAdminController',
            '--services' => 'admin.yml',
            '--id' => 'acme_demo_admin.admin.foo',
        ], ['interactive' => false]);
    }

    /**
     * @dataProvider getExecuteInteractiveTests
     */
    public function testExecuteInteractive($modelEntity)
    {
        mkdir($this->tempDirectory . '/Resources/config', 0777, true);

        $this->command->setContainer($this->container);
        $this->container->set('sonata.admin.manager.foo', $this->createMock('Sonata\AdminBundle\Model\ModelManagerInterface'));
        $this->container->set('sonata.admin.manager.bar', $this->createMock('Sonata\AdminBundle\Model\ModelManagerInterface'));

        $command = $this->application->find('sonata:admin:generate');

        // NEXT_MAJOR: Remove this BC code for SensioGeneratorBundle 2.3/2.4 after dropping support for Symfony 2.3
        // DialogHelper does not exist in SensioGeneratorBundle 2.5+
        if (class_exists('Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper')) {
            $dialog = $this->getMockBuilder('Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper')
                ->setMethods(['askConfirmation', 'askAndValidate'])
                ->getMock();

            $dialog->expects($this->any())
                ->method('askConfirmation')
                ->will($this->returnCallback(function (OutputInterface $output, $question, $default) {
                    $questionClean = substr($question, 6, strpos($question, '</info>') - 6);

                    switch ($questionClean) {
                        case 'Do you want to generate a controller':
                            return 'yes';

                        case 'Do you want to update the services YAML configuration file':
                            return 'yes';
                    }

                    return $default;
                }));

            $dialog->expects($this->any())
                ->method('askAndValidate')
                ->will($this->returnCallback(function (OutputInterface $output, $question, $validator, $attempts = false, $default = null) use ($modelEntity) {
                    $questionClean = substr($question, 6, strpos($question, '</info>') - 6);

                    switch ($questionClean) {
                        case 'The fully qualified model class':
                            return $modelEntity;

                        case 'The bundle name':
                            return 'DemoAdminBundle';

                        case 'The admin class basename':
                            return 'FooAdmin';

                        case 'The controller class basename':
                            return 'FooAdminController';

                        case 'The services YAML configuration file':
                            return 'admin.yml';

                        case 'The admin service ID':
                            return 'acme_demo_admin.admin.foo';

                        case 'The manager type':
                            return 'foo';
                    }

                    return $default;
                }));

            $command->getHelperSet()->set($dialog, 'dialog');
        } else {
            $questionHelper = $this->getMockBuilder('Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper')
                ->setMethods(['ask'])
                ->getMock();

            $questionHelper->expects($this->any())
                ->method('ask')
                ->will($this->returnCallback(function (InputInterface $input, OutputInterface $output, Question $question) use ($modelEntity) {
                    $questionClean = substr($question->getQuestion(), 6, strpos($question->getQuestion(), '</info>') - 6);

                    switch ($questionClean) {
                        // confirmations
                        case 'Do you want to generate a controller':
                            return 'yes';

                        case 'Do you want to update the services YAML configuration file':
                            return 'yes';

                        // inputs
                        case 'The fully qualified model class':
                            return $modelEntity;

                        case 'The bundle name':
                            return 'DemoAdminBundle';

                        case 'The admin class basename':
                            return 'FooAdmin';

                        case 'The controller class basename':
                            return 'FooAdminController';

                        case 'The services YAML configuration file':
                            return 'admin.yml';

                        case 'The admin service ID':
                            return 'acme_demo_admin.admin.foo';

                        case 'The manager type':
                            return 'foo';
                    }

                    return false;
                }));

            $command->getHelperSet()->set($questionHelper, 'question');
        }

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'model' => $modelEntity,
            ]);

        $expectedOutput = PHP_EOL.str_pad('', 41, ' ').PHP_EOL.'  Welcome to the Sonata admin generator  '.PHP_EOL.str_pad('', 41, ' ').PHP_EOL.PHP_EOL;
        $expectedOutput .= sprintf('%3$sThe admin class "Sonata\AdminBundle\Tests\Fixtures\Bundle\Admin\FooAdmin" has been generated under the file "%1$s%2$sAdmin%2$sFooAdmin.php".%3$s', $this->tempDirectory, DIRECTORY_SEPARATOR, PHP_EOL);
        $expectedOutput .= sprintf('%3$sThe controller class "Sonata\AdminBundle\Tests\Fixtures\Bundle\Controller\FooAdminController" has been generated under the file "%1$s%2$sController%2$sFooAdminController.php".%3$s', $this->tempDirectory, DIRECTORY_SEPARATOR, PHP_EOL);
        $expectedOutput .= sprintf('%3$sThe service "acme_demo_admin.admin.foo" has been appended to the file "%1$s%2$sResources%2$sconfig%2$sadmin.yml".%3$s', $this->tempDirectory, DIRECTORY_SEPARATOR, PHP_EOL);

        $this->assertSame($expectedOutput, str_replace("\n", PHP_EOL, str_replace(PHP_EOL, "\n", $commandTester->getDisplay())));

        $this->assertFileExists($this->tempDirectory.'/Admin/FooAdmin.php');
        $this->assertFileExists($this->tempDirectory.'/Controller/FooAdminController.php');
        $this->assertFileExists($this->tempDirectory.'/Resources/config/admin.yml');

        $adminContent = file_get_contents($this->tempDirectory.'/Admin/FooAdmin.php');
        $this->assertContains('class FooAdmin extends AbstractAdmin', $adminContent);
        $this->assertContains('use Sonata\AdminBundle\Admin\AbstractAdmin;', $adminContent);
        $this->assertContains('use Sonata\AdminBundle\Datagrid\DatagridMapper;', $adminContent);
        $this->assertContains('use Sonata\AdminBundle\Datagrid\ListMapper;', $adminContent);
        $this->assertContains('use Sonata\AdminBundle\Form\FormMapper;', $adminContent);
        $this->assertContains('use Sonata\AdminBundle\Show\ShowMapper;', $adminContent);
        $this->assertContains('protected function configureDatagridFilters(DatagridMapper $datagridMapper)', $adminContent);
        $this->assertContains('protected function configureListFields(ListMapper $listMapper)', $adminContent);
        $this->assertContains('protected function configureFormFields(FormMapper $formMapper)', $adminContent);
        $this->assertContains('protected function configureShowFields(ShowMapper $showMapper)', $adminContent);

        $controllerContent = file_get_contents($this->tempDirectory.'/Controller/FooAdminController.php');
        $this->assertContains('class FooAdminController extends CRUDController', $controllerContent);
        $this->assertContains('use Sonata\AdminBundle\Controller\CRUDController;', $controllerContent);

        $configServiceContent = file_get_contents($this->tempDirectory.'/Resources/config/admin.yml');
        $this->assertContains('services:'."\n".'    acme_demo_admin.admin.foo', $configServiceContent);
        $this->assertContains('            - { name: sonata.admin, manager_type: foo, group: admin, label: Foo }', $configServiceContent);
    }

    public function getExecuteInteractiveTests()
    {
        return [
            ['Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo'],
            ['Sonata\AdminBundle\Tests\Fixtures\Entity\Foo'],
        ];
    }

    /**
     * @dataProvider getValidateManagerTypeTests
     */
    public function testValidateManagerType($expected, $managerType)
    {
        $this->command->setContainer($this->container);
        $this->container->set('sonata.admin.manager.foo', $this->createMock('Sonata\AdminBundle\Model\ModelManagerInterface'));
        $this->container->set('sonata.admin.manager.bar', $this->createMock('Sonata\AdminBundle\Model\ModelManagerInterface'));

        $this->assertSame($expected, $this->command->validateManagerType($managerType));
    }

    public function getValidateManagerTypeTests()
    {
        return [
            ['foo', 'foo'],
            ['bar', 'bar'],
        ];
    }

    public function testValidateManagerTypeWithException1()
    {
        $this->command->setContainer($this->container);
        $this->setExpectedException('InvalidArgumentException', 'Invalid manager type "foo". Available manager types are "".');
        $this->command->validateManagerType('foo');
    }

    public function testValidateManagerTypeWithException2()
    {
        $this->command->setContainer($this->container);
        $this->container->set('sonata.admin.manager.foo', $this->createMock('Sonata\AdminBundle\Model\ModelManagerInterface'));
        $this->container->set('sonata.admin.manager.bar', $this->createMock('Sonata\AdminBundle\Model\ModelManagerInterface'));

        $this->expectException('InvalidArgumentException', 'Invalid manager type "baz". Available manager types are "foo", "bar".');
        $this->command->validateManagerType('baz');
    }

    public function testValidateManagerTypeWithException3()
    {
        $this->expectException('InvalidArgumentException', 'Invalid manager type "baz". Available manager types are "".');
        $this->command->validateManagerType('baz');
    }

    public function testAnswerUpdateServicesWithNo()
    {
        $this->container->set('sonata.admin.manager.foo', $this->createMock('Sonata\AdminBundle\Model\ModelManagerInterface'));

        $modelEntity = 'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo';

        $command = $this->application->find('sonata:admin:generate');

        // NEXT_MAJOR: Remove this BC code for SensioGeneratorBundle 2.3/2.4 after dropping support for Symfony 2.3
        // DialogHelper does not exist in SensioGeneratorBundle 2.5+
        if (class_exists('Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper')) {
            $dialog = $this->getMockBuilder('Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper')
                ->setMethods(['askConfirmation', 'askAndValidate'])
                ->getMock();

            $dialog->expects($this->any())
                ->method('askConfirmation')
                ->will($this->returnCallback(function (OutputInterface $output, $question, $default) {
                    $questionClean = substr($question, 6, strpos($question, '</info>') - 6);

                    switch ($questionClean) {
                        case 'Do you want to generate a controller':
                            return false;

                        case 'Do you want to update the services YAML configuration file':
                            return false;
                    }

                    return $default;
                }));

            $dialog->expects($this->any())
                ->method('askAndValidate')
                ->will($this->returnCallback(function (OutputInterface $output, $question, $validator, $attempts = false, $default = null) use ($modelEntity) {
                    $questionClean = substr($question, 6, strpos($question, '</info>') - 6);

                    switch ($questionClean) {
                        case 'The fully qualified model class':
                            return $modelEntity;

                        case 'The bundle name':
                            return 'DemoAdminBundle';

                        case 'The admin class basename':
                            return 'FooAdmin';

                        case 'The controller class basename':
                            return 'FooAdminController';

                        case 'The services YAML configuration file':
                            return 'admin.yml';

                        case 'The admin service ID':
                            return 'acme_demo_admin.admin.foo';

                        case 'The manager type':
                            return 'foo';
                    }

                    return $default;
                }));

            $command->getHelperSet()->set($dialog, 'dialog');
        } else {
            $questionHelper = $this->getMockBuilder('Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper')
                ->setMethods(['ask'])
                ->getMock();

            $questionHelper->expects($this->any())
                ->method('ask')
                ->will($this->returnCallback(function (InputInterface $input, OutputInterface $output, Question $question) use ($modelEntity) {
                    $questionClean = substr($question->getQuestion(), 6, strpos($question->getQuestion(), '</info>') - 6);

                    switch ($questionClean) {
                        // confirmations
                        case 'Do you want to generate a controller':
                            return false;

                        case 'Do you want to update the services YAML configuration file':
                            return false;

                        // inputs
                        case 'The fully qualified model class':
                            return $modelEntity;

                        case 'The bundle name':
                            return 'DemoAdminBundle';

                        case 'The admin class basename':
                            return 'FooAdmin';
                    }

                    return false;
                }));

            $command->getHelperSet()->set($questionHelper, 'question');
        }

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'model' => $modelEntity,
        ]);

        $this->assertFalse(file_exists($this->tempDirectory.'/Resources/config/services.yml'));
    }

    public function testWriteXMLServiceDefinitionToApplicationConfigs()
    {
        mkdir($this->tempDirectory . '/config');
        $emptyServiceDefinition = <<<XML
<?xml version="1.0" ?>
<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <services>
    </services>
</container>
XML;

        file_put_contents($this->tempDirectory . '/config/services.xml', $emptyServiceDefinition);

        $this->command->setContainer($this->container);
        $this->container->set('sonata.admin.manager.foo', $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface'));
        $this->container->set('sonata.admin.manager.bar', $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface'));

        $commandTester = $this->setupAndRunCommandTester('services.xml');

        $expectedOutput = PHP_EOL.str_pad('', 41, ' ').PHP_EOL.'  Welcome to the Sonata admin generator  '.PHP_EOL.str_pad('', 41, ' ').PHP_EOL.PHP_EOL;
        $expectedOutput .= sprintf('%3$sThe admin class "Sonata\AdminBundle\Tests\Fixtures\Bundle\Admin\FooAdmin" has been generated under the file "%1$s%2$sAdmin%2$sFooAdmin.php".%3$s', $this->tempDirectory, DIRECTORY_SEPARATOR, PHP_EOL);
        $expectedOutput .= sprintf('%3$sThe controller class "Sonata\AdminBundle\Tests\Fixtures\Bundle\Controller\FooAdminController" has been generated under the file "%1$s%2$sController%2$sFooAdminController.php".%3$s', $this->tempDirectory, DIRECTORY_SEPARATOR, PHP_EOL);
        $expectedOutput .= sprintf('%3$sThe service "acme_demo_admin.admin.foo" has been appended to the file "%1$s%2$sconfig%2$sservices.xml".%3$s', $this->tempDirectory, DIRECTORY_SEPARATOR, PHP_EOL);

        $this->assertSame($expectedOutput, str_replace("\n", PHP_EOL, str_replace(PHP_EOL, "\n", $commandTester->getDisplay())));

        $this->assertFileExists($this->tempDirectory.'/config/services.xml');

        $expectedServiceDefnition = <<<XML
<?xml version="1.0" ?>
<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <services>
        <service id="acme_demo_admin.admin.foo" class="Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo">
            <argument />
            <argument>Sonata\AdminBundle\Tests\Fixtures\Bundle\Admin\FooAdmin</argument>
            <argument>DemoAdminBundle:FooAdmin</argument>

            <tag name="sonata.admin" manager_type="foo" group="admin" label="FooAdmin" />
        </service>
        
    </services>
</container>
XML;

        $configServiceContent = file_get_contents($this->tempDirectory.'/config/services.xml');

        $this->assertSame($expectedServiceDefnition, $configServiceContent);
    }

    public function testWriteXMLServiceDefinitionToBundleConfigs()
    {
        mkdir($this->tempDirectory . '/Resources/config', 0777, true);

        $this->command->setContainer($this->container);
        $this->container->set('sonata.admin.manager.foo', $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface'));
        $this->container->set('sonata.admin.manager.bar', $this->getMock('Sonata\AdminBundle\Model\ModelManagerInterface'));

        $this->setupAndRunCommandTester('services.xml');

        $this->assertFileExists($this->tempDirectory.'/Resources/config/services.xml');

        $expectedServiceDefnition = <<<XML
<?xml version="1.0" ?>
<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <services>
        <service id="acme_demo_admin.admin.foo" class="Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo">
            <argument />
            <argument>Sonata\AdminBundle\Tests\Fixtures\Bundle\Admin\FooAdmin</argument>
            <argument>DemoAdminBundle:FooAdmin</argument>

            <tag name="sonata.admin" manager_type="foo" group="admin" label="FooAdmin" />
        </service>
        
    </services>
</container>
XML;

        $configServiceContent = file_get_contents($this->tempDirectory.'/Resources/config/services.xml');

        $this->assertSame($expectedServiceDefnition, $configServiceContent);
    }

    /**
     * @param string $targetConfigFileName
     *
     * @return CommandTester
     */
    private function setupAndRunCommandTester($targetConfigFileName)
    {
        $command = $this->application->find('sonata:admin:generate');

        $modelEntity = 'Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Foo';

        // NEXT_MAJOR: Remove this BC code for SensioGeneratorBundle 2.3/2.4 after dropping support for Symfony 2.3
        // DialogHelper does not exist in SensioGeneratorBundle 2.5+
        if (class_exists('Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper')) {
            $dialog = $this->getMock('Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper', array('askConfirmation', 'askAndValidate'));

            $dialog->expects($this->any())
                ->method('askConfirmation')
                ->will($this->returnCallback(function (OutputInterface $output, $question, $default) {
                    $questionClean = substr($question, 6, strpos($question, '</info>') - 6);

                    switch ($questionClean) {
                        case 'Do you want to generate a controller':
                            return 'yes';

                        case 'Do you want to update the services YAML configuration file':
                            return 'yes';
                    }

                    return $default;
                }));

            $dialog->expects($this->any())
                ->method('askAndValidate')
                ->will($this->returnCallback(function (OutputInterface $output, $question, $validator, $attempts = false, $default = null) use ($modelEntity, $targetConfigFileName) {
                    $questionClean = substr($question, 6, strpos($question, '</info>') - 6);

                    switch ($questionClean) {
                        case 'The fully qualified model class':
                            return $modelEntity;

                        case 'The bundle name':
                            return 'DemoAdminBundle';

                        case 'The admin class basename':
                            return 'FooAdmin';

                        case 'The controller class basename':
                            return 'FooAdminController';

                        case 'The services YAML configuration file':
                            return $targetConfigFileName;

                        case 'The admin service ID':
                            return 'acme_demo_admin.admin.foo';

                        case 'The manager type':
                            return 'foo';
                    }

                    return $default;
                }));

            $command->getHelperSet()->set($dialog, 'dialog');
        } else {
            $questionHelper = $this->getMock('Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper', array('ask'));

            $questionHelper->expects($this->any())
                ->method('ask')
                ->will($this->returnCallback(function (InputInterface $input, OutputInterface $output, Question $question) use ($modelEntity, $targetConfigFileName) {
                    $questionClean = substr($question->getQuestion(), 6, strpos($question->getQuestion(), '</info>') - 6);

                    switch ($questionClean) {
                        // confirmations
                        case 'Do you want to generate a controller':
                            return 'yes';

                        case 'Do you want to update the services YAML configuration file':
                            return 'yes';

                        // inputs
                        case 'The fully qualified model class':
                            return $modelEntity;

                        case 'The bundle name':
                            return 'DemoAdminBundle';

                        case 'The admin class basename':
                            return 'FooAdmin';

                        case 'The controller class basename':
                            return 'FooAdminController';

                        case 'The services YAML configuration file':
                            return $targetConfigFileName;

                        case 'The admin service ID':
                            return 'acme_demo_admin.admin.foo';

                        case 'The manager type':
                            return 'foo';
                    }

                    return false;
                }));

            $command->getHelperSet()->set($questionHelper, 'question');
        }

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'model' => $modelEntity,
        ));

        return $commandTester;
    }
}
