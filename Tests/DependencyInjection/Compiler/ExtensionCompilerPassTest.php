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
use Sonata\AdminBundle\Admin\Admin;

class ExtensionCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /** @var SonataAdminExtension $extension */
    private $extension;

    /** @var array $config */
    private $config;

    private $publishExtension;
    private $historyExtension;
    private $orderExtension;
    private $securityExtension;
    private $filterExtension;

    /**
     * Root name of the configuration
     *
     * @var string
     */
    private $root;

    public function setUp()
    {
        parent::setUp();

        $this->extension = new SonataAdminExtension();
        $this->config    = $this->getConfig();
        $this->root      = 'sonata.admin';

        $this->publishExtension = $this->getMock('Sonata\AdminBundle\Admin\AdminExtensionInterface');
        $this->historyExtension = $this->getMock('Sonata\AdminBundle\Admin\AdminExtensionInterface');
        $this->orderExtension = $this->getMock('Sonata\AdminBundle\Admin\AdminExtensionInterface');
        $this->securityExtension = $this->getMock('Sonata\AdminBundle\Admin\AdminExtensionInterface');
        $this->filterExtension = $this->getMock('Sonata\AdminBundle\Admin\AdminExtensionInterface');
    }

    /**
     * @covers Sonata\AdminBundle\DependencyInjection\SonataAdminExtension::load
     */
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
    public function testFlattenEmptyExtensionConfiguration()
    {
        $this->extension->load(array(), $container = $this->getContainer());
        $extensionMap = $container->getParameter($this->root . ".extension.map");

        $method = new \ReflectionMethod(
          'Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass', 'flattenExtensionConfiguration'
        );

        $method->setAccessible(TRUE);
        $extensionMap = $method->invokeArgs(new ExtensionCompilerPass(), array($extensionMap));

        $this->assertArrayHasKey('admins', $extensionMap);
        $this->assertArrayHasKey('excludes', $extensionMap);
        $this->assertArrayHasKey('implements', $extensionMap);
        $this->assertArrayHasKey('extends', $extensionMap);
        $this->assertArrayHasKey('instanceof', $extensionMap);

        $this->assertEmpty($extensionMap['admins']);
        $this->assertEmpty($extensionMap['excludes']);
        $this->assertEmpty($extensionMap['implements']);
        $this->assertEmpty($extensionMap['extends']);
        $this->assertEmpty($extensionMap['instanceof']);
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

        // Admins
        $this->assertArrayHasKey('admins', $extensionMap);
        $this->assertCount(1, $extensionMap['admins']);

        $this->assertContains('sonata_extension_publish', $extensionMap['admins']['sonata_post_admin']);
        $this->assertCount(1, $extensionMap['admins']['sonata_post_admin']);

        // Excludes
        $this->assertArrayHasKey('excludes', $extensionMap);
        $this->assertCount(2, $extensionMap['excludes']);

        $this->assertArrayHasKey('sonata_article_admin', $extensionMap['excludes']);
        $this->assertCount(1, $extensionMap['excludes']['sonata_article_admin']);
        $this->assertContains('sonata_extension_history', $extensionMap['excludes']['sonata_article_admin']);

        $this->assertArrayHasKey('sonata_post_admin', $extensionMap['excludes']);
        $this->assertCount(1, $extensionMap['excludes']['sonata_post_admin']);
        $this->assertContains('sonata_extension_order', $extensionMap['excludes']['sonata_post_admin']);

        // Implements
        $this->assertArrayHasKey('implements', $extensionMap);
        $this->assertCount(1, $extensionMap['implements']);

        $this->assertArrayHasKey('Sonata\AdminBundle\Tests\DependencyInjection\Publishable', $extensionMap['implements']);
        $this->assertCount(2, $extensionMap['implements']['Sonata\AdminBundle\Tests\DependencyInjection\Publishable']);
        $this->assertContains('sonata_extension_publish', $extensionMap['implements']['Sonata\AdminBundle\Tests\DependencyInjection\Publishable']);
        $this->assertContains('sonata_extension_order', $extensionMap['implements']['Sonata\AdminBundle\Tests\DependencyInjection\Publishable']);

        // Extends
        $this->assertArrayHasKey('extends', $extensionMap);
        $this->assertCount(1, $extensionMap['extends']);

        $this->assertArrayHasKey('Sonata\AdminBundle\Tests\DependencyInjection\Post', $extensionMap['extends']);
        $this->assertCount(1, $extensionMap['extends']['Sonata\AdminBundle\Tests\DependencyInjection\Post']);
        $this->assertContains('sonata_extension_order', $extensionMap['extends']['Sonata\AdminBundle\Tests\DependencyInjection\Post']);

        // Instanceof
        $this->assertArrayHasKey('instanceof', $extensionMap);
        $this->assertCount(1, $extensionMap['instanceof']);

        $this->assertArrayHasKey('Sonata\AdminBundle\Tests\DependencyInjection\Post', $extensionMap['instanceof']);
        $this->assertCount(1, $extensionMap['instanceof']['Sonata\AdminBundle\Tests\DependencyInjection\Post']);
        $this->assertContains('sonata_extension_history', $extensionMap['instanceof']['Sonata\AdminBundle\Tests\DependencyInjection\Post']);
    }

    /**
     * @covers Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass::process
     * @expectedException \InvalidArgumentException
     */
    public function testProcessWithInvalidExtensionId()
    {
        $config = array(
            'extensions' => array(
                'sonata_extension_unknown' => array(
                    'excludes' => array('sonata_article_admin'),
                    'instanceof' => array('Sonata\AdminBundle\Tests\DependencyInjection\Post'),
                ),
            )
        );

        $container = $this->getContainer();
        $this->extension->load(array($config), $container);

        $extensionsPass = new ExtensionCompilerPass();
        $extensionsPass->process($container);
        $container->compile();
    }

    /**
     * @covers Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass::process
     */
    public function testProcessWithInvalidAdminId()
    {
        $config = array(
            'extensions' => array(
                'sonata_extension_publish' => array(
                    'admins' => array('sonata_unknown_admin'),
                    'implements' => array('Sonata\AdminBundle\Tests\DependencyInjection\Publishable'),
                ),
            )
        );

        $container = $this->getContainer();
        $this->extension->load(array($config), $container);

        $extensionsPass = new ExtensionCompilerPass();
        $extensionsPass->process($container);
        $container->compile();

        // nothing should fail the extension just isn't added to the 'sonata_unknown_admin'
    }

    /**
     * @covers Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass::process
     */
    public function testProcess()
    {
        $container = $this->getContainer();
        $this->extension->load(array($this->config), $container);

        $extensionsPass = new ExtensionCompilerPass();
        $extensionsPass->process($container);
        $container->compile();

        $this->assertTrue($container->hasDefinition('sonata_extension_publish'));
        $this->assertTrue($container->hasDefinition('sonata_extension_history'));
        $this->assertTrue($container->hasDefinition('sonata_extension_order'));
        $this->assertTrue($container->hasDefinition('sonata_extension_security'));

        $this->assertTrue($container->hasDefinition('sonata_post_admin'));
        $this->assertTrue($container->hasDefinition('sonata_article_admin'));
        $this->assertTrue($container->hasDefinition('sonata_news_admin'));

        $def = $container->get('sonata_post_admin');
        $extensions = $def->getExtensions();
        $this->assertCount(3, $extensions);

        $this->assertInstanceOf(get_class($this->securityExtension), $extensions[0]);
        $this->assertInstanceOf(get_class($this->publishExtension), $extensions[1]);
        $this->assertInstanceOf(get_class($this->historyExtension), $extensions[2]);

        $def = $container->get('sonata_article_admin');
        $extensions = $def->getExtensions();
        $this->assertCount(4, $extensions);
        $this->assertInstanceOf(get_class($this->securityExtension), $extensions[0]);
        $this->assertInstanceOf(get_class($this->publishExtension), $extensions[1]);
        $this->assertInstanceOf(get_class($this->orderExtension), $extensions[2]);
        $this->assertInstanceOf(get_class($this->filterExtension), $extensions[3]);

        $def = $container->get('sonata_news_admin');
        $extensions = $def->getExtensions();
        $this->assertCount(4, $extensions);
        $this->assertInstanceOf(get_class($this->securityExtension), $extensions[0]);
        $this->assertInstanceOf(get_class($this->orderExtension), $extensions[1]);
        $this->assertInstanceOf(get_class($this->historyExtension), $extensions[2]);
        $this->assertInstanceOf(get_class($this->filterExtension), $extensions[3]);
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        $config = array(
            'extensions' => array(
                'sonata_extension_publish' => array(
                    'admins' => array('sonata_post_admin'),
                    'implements' => array('Sonata\AdminBundle\Tests\DependencyInjection\Publishable'),
                ),
                'sonata_extension_history' => array(
                    'excludes' => array('sonata_article_admin'),
                    'instanceof' => array('Sonata\AdminBundle\Tests\DependencyInjection\Post'),
                ),
                'sonata_extension_order' => array(
                    'excludes' => array('sonata_post_admin'),
                    'extends' => array('Sonata\AdminBundle\Tests\DependencyInjection\Post'),
                    'implements' => array('Sonata\AdminBundle\Tests\DependencyInjection\Publishable'),
                ),
            )
        );
        return $config;
    }

    private function getContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array(
            'SonataCoreBundle' => true,
            'KnpMenuBundle' => true
        ));

        // Add dependencies for SonataAdminBundle (these services will never get called so dummy classes will do)
        $container
            ->register('twig')
            ->setClass('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $container
            ->register('templating')
            ->setClass('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $container
            ->register('translator')
            ->setClass('Symfony\Bundle\FrameworkBundle\Translation\TranslatorInterface');
        $container
            ->register('validator.validator_factory')
            ->setClass('Symfony\Bundle\FrameworkBundle\Validator\ConstraintValidatorFactory');
        $container
            ->register('router')
            ->setClass('Symfony\Component\Routing\RouterInterface');
        $container
            ->register('form.factory')
            ->setClass('Symfony\Component\Form\FormFactoryInterface');
        $container
            ->register('validator')
            ->setClass('Symfony\Component\Validator\ValidatorInterface');

        // Add admin definition's
        $container
            ->register('sonata_post_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\Post', 'SonataAdminBundle:CRUD'))
            ->addTag('sonata.admin');
        $container
            ->register('sonata_news_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\News', 'SonataAdminBundle:CRUD'))
            ->addTag('sonata.admin');
        $container
            ->register('sonata_article_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(array('', 'Sonata\AdminBundle\Tests\DependencyInjection\Article', 'SonataAdminBundle:CRUD'))
            ->addTag('sonata.admin');
        $container
            ->register('event_dispatcher')
            ->setClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        // Add admin extension definition's
        $container
            ->register('sonata_extension_publish')
            ->setClass(get_class($this->publishExtension));
        $container
            ->register('sonata_extension_history')
            ->setClass(get_class($this->historyExtension));
        $container
            ->register('sonata_extension_order')
            ->setClass(get_class($this->orderExtension));
        $container
            ->register('sonata_extension_security')
            ->setClass(get_class($this->securityExtension))
            ->addTag('sonata.admin.extension', array('global' => true));
        $container
            ->register('sonata_extension_filter')
            ->setClass(get_class($this->filterExtension))
            ->addTag('sonata.admin.extension', array('target' => 'sonata_news_admin'))
            ->addTag('sonata.admin.extension', array('target' => 'sonata_article_admin'));

        return $container;
    }
}

class MockAdmin extends Admin {}

class Post {}
interface Publishable {}
class News extends Post {}
class Article implements Publishable {}
