<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\DependencyInjection;

use Sonata\AdminBundle\DependencyInjection\SonataAdminExtension;
use Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sonata\AdminBundle\Tests\Model\MockExtension;
use Sonata\AdminBundle\Admin\Admin;

class ExtensionCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /** @var SonataAdminExtension $extension */
    private $extension;

    /** @var array $config */
    private $config;

    /**
     * Root name of the configuration
     *
     * @var string
     */
    private $root;

    public function setUp()
    {
        parent::setUp();

        $this->extension = $this->getExtension();
        $this->config    = $this->getConfig();
        $this->root      = "sonata.admin";
    }

    public function testAdminExtensionLoad()
    {
        $this->extension->load(array(), $container = $this->getContainer());

        $this->assertTrue($container->hasParameter($this->root . ".extension.map"));
        $this->assertTrue(is_array($extensionMap = $container->getParameter($this->root . ".extension.map")));

        $this->assertArrayHasKey('admins', $extensionMap);
        $this->assertArrayHasKey('excludes', $extensionMap);
        $this->assertArrayHasKey('implements', $extensionMap);
        $this->assertArrayHasKey('extends', $extensionMap);
        $this->assertArrayHasKey('instanceof', $extensionMap);
    }

    /**
     * @covers Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass::flattenExtensionConfiguration
     */
    public function testFlattenExtensionConfiguration()
    {
        $config = $this->getConfig();
        $this->extension->load(array($config), $container = $this->getContainer());
        $extensionMap = $container->getParameter($this->root . ".extension.map");

        $method = new \ReflectionMethod(
                  'Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass', 'flattenExtensionConfiguration'
                );

        $method->setAccessible(TRUE);
        $extensionMap = $method->invokeArgs(new ExtensionCompilerPass(), array($extensionMap));

        $this->assertTrue(isset($extensionMap['admins']['sonata_admin_1']));
        $this->assertTrue(count($extensionMap['admins']['sonata_admin_1']) == 1);
        $this->assertTrue(in_array('sonata_extension_1', $extensionMap['admins']['sonata_admin_1']));

        $this->assertTrue(isset($extensionMap['excludes']['sonata_admin_1']));
        $this->assertTrue(count($extensionMap['excludes']['sonata_admin_1']) == 2);
        $this->assertTrue(in_array('sonata_extension_2', $extensionMap['excludes']['sonata_admin_1']));
        $this->assertTrue(in_array('sonata_extension_3', $extensionMap['excludes']['sonata_admin_1']));

        $this->assertTrue(isset($extensionMap['excludes']['sonata_admin_2']));
        $this->assertTrue(count($extensionMap['excludes']['sonata_admin_2']) == 1);
        $this->assertTrue(in_array('sonata_extension_2', $extensionMap['excludes']['sonata_admin_2']));

        $this->assertTrue(isset($extensionMap['implements']['Sonata\AdminBundle\Tests\DependencyInjection\Interface1']));
        $this->assertTrue(count($extensionMap['implements']['Sonata\AdminBundle\Tests\DependencyInjection\Interface1']) == 2);
        $this->assertTrue(in_array('sonata_extension_1', $extensionMap['implements']['Sonata\AdminBundle\Tests\DependencyInjection\Interface1']));
        $this->assertTrue(in_array('sonata_extension_3', $extensionMap['implements']['Sonata\AdminBundle\Tests\DependencyInjection\Interface1']));

        $this->assertTrue(isset($extensionMap['extends']['Sonata\AdminBundle\Tests\DependencyInjection\SuperClass1']));
        $this->assertTrue(count($extensionMap['extends']['Sonata\AdminBundle\Tests\DependencyInjection\SuperClass1']) == 1);
        $this->assertTrue(in_array('sonata_extension_2', $extensionMap['extends']['Sonata\AdminBundle\Tests\DependencyInjection\SuperClass1']));

        $this->assertTrue(isset($extensionMap['extends']['Sonata\AdminBundle\Tests\DependencyInjection\SuperClass2']));
        $this->assertTrue(count($extensionMap['extends']['Sonata\AdminBundle\Tests\DependencyInjection\SuperClass2']) == 1);
        $this->assertTrue(in_array('sonata_extension_3', $extensionMap['extends']['Sonata\AdminBundle\Tests\DependencyInjection\SuperClass2']));

        $this->assertTrue(isset($extensionMap['instanceof']['Sonata\AdminBundle\Tests\DependencyInjection\SuperClass2']));
        $this->assertTrue(count($extensionMap['instanceof']['Sonata\AdminBundle\Tests\DependencyInjection\SuperClass2']) == 2);
        $this->assertTrue(in_array('sonata_extension_3', $extensionMap['instanceof']['Sonata\AdminBundle\Tests\DependencyInjection\SuperClass2']));
        $this->assertTrue(in_array('sonata_extension_2', $extensionMap['instanceof']['Sonata\AdminBundle\Tests\DependencyInjection\SuperClass2']));
    }

    public function testGetExtensionsForAdmin()
    {
        $container = $this->getContainer();
        $this->extension->load(array($this->config), $container);

        $extensionsPass = new ExtensionCompilerPass();
        $extensionsPass->process($container);
        $container->compile();

        $this->assertTrue($container->hasDefinition('sonata_extension_1'));
        $this->assertTrue($container->hasDefinition('sonata_extension_2'));
        $this->assertTrue($container->hasDefinition('sonata_extension_3'));

        $this->assertTrue($container->hasDefinition('sonata_admin_1'));
        $this->assertTrue($container->hasDefinition('sonata_admin_2'));
        $this->assertTrue($container->hasDefinition('sonata_admin_3'));


        $def = $container->get('sonata_admin_1');
        $extensions = $def->getExtensions();
        $this->assertTrue($extensions[0] instanceof \Sonata\AdminBundle\Tests\DependencyInjection\MockExtension1);

        $def = $container->get('sonata_admin_2');
        $extensions = $def->getExtensions();
        $this->assertTrue(empty($extensions));

        $def = $container->get('sonata_admin_3');
        $extensions = $def->getExtensions();
        $this->assertTrue($extensions[0] instanceof \Sonata\AdminBundle\Tests\DependencyInjection\MockExtension1);
        $this->assertTrue($extensions[1] instanceof \Sonata\AdminBundle\Tests\DependencyInjection\MockExtension3);
    }

    /**
     * @return SonataAdminExtension
     */
    protected function getExtension()
    {
        return new SonataAdminExtension();
    }

    /**
     * Returns the Configuration to test
     *
     * @return SonataAdminExtension
     */
    protected function getConfig()
    {
        $config = array(
            'extensions' => array(
                'sonata_extension_1' => array(
                    'admins' => array('sonata_admin_1'),
                    'implements' => array('Sonata\AdminBundle\Tests\DependencyInjection\Interface1'),
                ),
                'sonata_extension_2' => array(
                    'excludes' => array('sonata_admin_1', 'sonata_admin_2'),
                    'extends' => array('Sonata\AdminBundle\Tests\DependencyInjection\SuperClass1'),
                    'instanceof' => array('Sonata\AdminBundle\Tests\DependencyInjection\SuperClass2'),
                ),
                'sonata_extension_3' => array(
                    'excludes' => array('sonata_admin_1'),
                    'extends' => array('Sonata\AdminBundle\Tests\DependencyInjection\SuperClass2'),
                    'instanceof' => array('Sonata\AdminBundle\Tests\DependencyInjection\SuperClass2'),
                    'implements' => array('Sonata\AdminBundle\Tests\DependencyInjection\Interface1'),
                ),
            )
        );
        return $config;
    }

    private function getContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array());

        $container
            ->register('twig')
            ->setClass('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $container
            ->register('templating')
            ->setClass('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $container
            ->register('translator')
            ->setClass('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $container
            ->register('validator.validator_factory')
            ->setClass('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $container
            ->register('router')
            ->setClass('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');


        $container
            ->register('sonata_admin_1')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\SuperClass1', 'SonataAdminBundle:CRUD'))
            ->addTag('sonata.admin');
        $container
                ->register('sonata_admin_2')
                ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
                ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\SubClass1', 'SonataAdminBundle:CRUD'))
                ->addTag('sonata.admin');
        $container
                ->register('sonata_admin_3')
                ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
                ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\SubClass2', 'SonataAdminBundle:CRUD'))
                ->addTag('sonata.admin');

        $container
            ->register('sonata_extension_1')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockExtension1');
        $container
            ->register('sonata_extension_2')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockExtension2');
        $container
            ->register('sonata_extension_3')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockExtension3');

        return $container;
    }
}

class MockExtension1 extends MockExtension {}
class MockExtension2 extends MockExtension {}
class MockExtension3 extends MockExtension {}
class MockAdmin extends Admin {}
class SuperClass1 {}
class SuperClass2 {}
interface Interface1 {}
class SubClass1 extends SuperClass2 {}
class SubClass2 implements Interface1 {}
