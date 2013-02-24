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
use Sonata\AdminBundle\DependencyInjection\Configuration;
use Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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

        $this->assertTrue(isset($extensionMap['implements']['Interface1']));
        $this->assertTrue(count($extensionMap['implements']['Interface1']) == 2);
        $this->assertTrue(in_array('sonata_extension_1', $extensionMap['implements']['Interface1']));
        $this->assertTrue(in_array('sonata_extension_3', $extensionMap['implements']['Interface1']));
        
        $this->assertTrue(isset($extensionMap['extends']['SuperClass1']));
        $this->assertTrue(count($extensionMap['extends']['SuperClass1']) == 1);
        $this->assertTrue(in_array('sonata_extension_2', $extensionMap['extends']['SuperClass1']));
        
        $this->assertTrue(isset($extensionMap['extends']['SuperClass2']));
        $this->assertTrue(count($extensionMap['extends']['SuperClass2']) == 1);
        $this->assertTrue(in_array('sonata_extension_3', $extensionMap['extends']['SuperClass2']));
        
        $this->assertTrue(isset($extensionMap['instanceof']['SuperClass2']));
        $this->assertTrue(count($extensionMap['instanceof']['SuperClass2']) == 2);
        $this->assertTrue(in_array('sonata_extension_3', $extensionMap['instanceof']['SuperClass2']));
        $this->assertTrue(in_array('sonata_extension_2', $extensionMap['instanceof']['SuperClass2']));
    }

    public function testGetExtensionsForAdmin()
    {
        $this->assertTrue(true);
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
                    'implements' => array('Interface1'),
                ),
                'sonata_extension_2' => array(
                    'excludes' => array('sonata_admin_1', 'sonata_admin_2'),
                    'extends' => array('SuperClass1'),
                    'instanceof' => array('SuperClass2'),
                ),
                'sonata_extension_3' => array(
                    'excludes' => array('sonata_admin_1'),
                    'extends' => array('SuperClass2'),
                    'instanceof' => array('SuperClass2'),
                    'implements' => array('Interface1'),
                ),
            )
        );
        return $config;
    }

    /**
     * @return ContainerBuilder
     */
    private function getContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array());

        return $container;
    }
}
