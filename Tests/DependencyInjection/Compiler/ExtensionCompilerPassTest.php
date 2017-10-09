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

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use Sonata\AdminBundle\DependencyInjection\SonataAdminExtension;
use Sonata\AdminBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ExtensionCompilerPassTest extends PHPUnit_Framework_TestCase
{
    /** @var SonataAdminExtension $extension */
    private $extension;

    /** @var array $config */
    private $config;

    private $hasTraits;

    /**
     * Root name of the configuration.
     *
     * @var string
     */
    private $root;

    public function setUp()
    {
        $this->extension = new SonataAdminExtension();
        $this->config = $this->getConfig();
        $this->root = 'sonata.admin';
        $this->hasTraits = version_compare(PHP_VERSION, '5.4.0', '>=');
    }

    /**
     * @covers \Sonata\AdminBundle\DependencyInjection\SonataAdminExtension::load
     */
    public function testAdminExtensionLoad()
    {
        $this->extension->load([], $container = $this->getContainer());

        $this->assertTrue($container->hasParameter($this->root.'.extension.map'));
        $this->assertTrue(is_array($extensionMap = $container->getParameter($this->root.'.extension.map')));

        $this->assertArrayHasKey('admins', $extensionMap);
        $this->assertArrayHasKey('excludes', $extensionMap);
        $this->assertArrayHasKey('implements', $extensionMap);
        $this->assertArrayHasKey('extends', $extensionMap);
        $this->assertArrayHasKey('instanceof', $extensionMap);
        $this->assertArrayHasKey('uses', $extensionMap);
    }

    /**
     * @covers \Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass::flattenExtensionConfiguration
     */
    public function testFlattenEmptyExtensionConfiguration()
    {
        $this->extension->load([], $container = $this->getContainer());
        $extensionMap = $container->getParameter($this->root.'.extension.map');

        $method = new \ReflectionMethod(
            'Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass', 'flattenExtensionConfiguration'
        );

        $method->setAccessible(true);
        $extensionMap = $method->invokeArgs(new ExtensionCompilerPass(), [$extensionMap]);

        $this->assertArrayHasKey('admins', $extensionMap);
        $this->assertArrayHasKey('excludes', $extensionMap);
        $this->assertArrayHasKey('implements', $extensionMap);
        $this->assertArrayHasKey('extends', $extensionMap);
        $this->assertArrayHasKey('instanceof', $extensionMap);
        $this->assertArrayHasKey('uses', $extensionMap);

        $this->assertEmpty($extensionMap['admins']);
        $this->assertEmpty($extensionMap['excludes']);
        $this->assertEmpty($extensionMap['implements']);
        $this->assertEmpty($extensionMap['extends']);
        $this->assertEmpty($extensionMap['instanceof']);
        $this->assertEmpty($extensionMap['uses']);
    }

    /**
     * @covers \Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass::flattenExtensionConfiguration
     */
    public function testFlattenExtensionConfiguration()
    {
        $config = $this->getConfig();
        $this->extension->load([$config], $container = $this->getContainer());
        $extensionMap = $container->getParameter($this->root.'.extension.map');

        $method = new \ReflectionMethod(
            'Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass', 'flattenExtensionConfiguration'
        );

        $method->setAccessible(true);
        $extensionMap = $method->invokeArgs(new ExtensionCompilerPass(), [$extensionMap]);

        // Admins
        $this->assertArrayHasKey('admins', $extensionMap);
        $this->assertCount(1, $extensionMap['admins']);

        $this->assertArrayHasKey('sonata_extension_publish', $extensionMap['admins']['sonata_post_admin']);
        $this->assertCount(1, $extensionMap['admins']['sonata_post_admin']);

        // Excludes
        $this->assertArrayHasKey('excludes', $extensionMap);
        $this->assertCount(2, $extensionMap['excludes']);

        $this->assertArrayHasKey('sonata_article_admin', $extensionMap['excludes']);
        $this->assertCount(1, $extensionMap['excludes']['sonata_article_admin']);
        $this->assertArrayHasKey('sonata_extension_history', $extensionMap['excludes']['sonata_article_admin']);

        $this->assertArrayHasKey('sonata_post_admin', $extensionMap['excludes']);
        $this->assertCount(1, $extensionMap['excludes']['sonata_post_admin']);
        $this->assertArrayHasKey('sonata_extension_order', $extensionMap['excludes']['sonata_post_admin']);

        // Implements
        $this->assertArrayHasKey('implements', $extensionMap);
        $this->assertCount(1, $extensionMap['implements']);

        $this->assertArrayHasKey('Sonata\AdminBundle\Tests\DependencyInjection\Publishable', $extensionMap['implements']);
        $this->assertCount(2, $extensionMap['implements']['Sonata\AdminBundle\Tests\DependencyInjection\Publishable']);
        $this->assertArrayHasKey('sonata_extension_publish', $extensionMap['implements']['Sonata\AdminBundle\Tests\DependencyInjection\Publishable']);
        $this->assertArrayHasKey('sonata_extension_order', $extensionMap['implements']['Sonata\AdminBundle\Tests\DependencyInjection\Publishable']);

        // Extends
        $this->assertArrayHasKey('extends', $extensionMap);
        $this->assertCount(1, $extensionMap['extends']);

        $this->assertArrayHasKey('Sonata\AdminBundle\Tests\DependencyInjection\Post', $extensionMap['extends']);
        $this->assertCount(1, $extensionMap['extends']['Sonata\AdminBundle\Tests\DependencyInjection\Post']);
        $this->assertArrayHasKey('sonata_extension_order', $extensionMap['extends']['Sonata\AdminBundle\Tests\DependencyInjection\Post']);

        // Instanceof
        $this->assertArrayHasKey('instanceof', $extensionMap);
        $this->assertCount(1, $extensionMap['instanceof']);

        $this->assertArrayHasKey('Sonata\AdminBundle\Tests\DependencyInjection\Post', $extensionMap['instanceof']);
        $this->assertCount(1, $extensionMap['instanceof']['Sonata\AdminBundle\Tests\DependencyInjection\Post']);
        $this->assertArrayHasKey('sonata_extension_history', $extensionMap['instanceof']['Sonata\AdminBundle\Tests\DependencyInjection\Post']);

        // Uses
        $this->assertArrayHasKey('uses', $extensionMap);

        if ($this->hasTraits) {
            $this->assertCount(1, $extensionMap['uses']);
            $this->assertArrayHasKey('Sonata\AdminBundle\Tests\Fixtures\DependencyInjection\TimestampableTrait', $extensionMap['uses']);
            $this->assertCount(1, $extensionMap['uses']['Sonata\AdminBundle\Tests\Fixtures\DependencyInjection\TimestampableTrait']);
            $this->assertArrayHasKey('sonata_extension_post', $extensionMap['uses']['Sonata\AdminBundle\Tests\Fixtures\DependencyInjection\TimestampableTrait']);
        } else {
            $this->assertCount(0, $extensionMap['uses']);
            $this->assertArrayNotHasKey('Sonata\AdminBundle\Tests\Fixtures\DependencyInjection\TimestampableTrait', $extensionMap['uses']);
        }
    }

    /**
     * @covers \Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass::process
     * @expectedException \InvalidArgumentException
     */
    public function testProcessWithInvalidExtensionId()
    {
        $config = [
            'extensions' => [
                'sonata_extension_unknown' => [
                    'excludes' => ['sonata_article_admin'],
                    'instanceof' => ['Sonata\AdminBundle\Tests\DependencyInjection\Post'],
                ],
            ],
        ];

        $container = $this->getContainer();
        $this->extension->load([$config], $container);

        $extensionsPass = new ExtensionCompilerPass();
        $extensionsPass->process($container);
        $container->compile();
    }

    /**
     * @covers \Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass::process
     */
    public function testProcessWithInvalidAdminId()
    {
        $config = [
            'extensions' => [
                'sonata_extension_publish' => [
                    'admins' => ['sonata_unknown_admin'],
                    'implements' => ['Sonata\AdminBundle\Tests\DependencyInjection\Publishable'],
                ],
            ],
        ];

        $container = $this->getContainer();
        $this->extension->load([$config], $container);

        $extensionsPass = new ExtensionCompilerPass();
        $extensionsPass->process($container);
        $container->compile();

        // nothing should fail the extension just isn't added to the 'sonata_unknown_admin'
    }

    /**
     * @covers \Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass::process
     */
    public function testProcess()
    {
        $container = $this->getContainer();
        $this->extension->load([$this->config], $container);

        $extensionsPass = new ExtensionCompilerPass();
        $extensionsPass->process($container);
        $container->compile();

        $this->assertTrue($container->hasDefinition('sonata_extension_publish'));
        $this->assertTrue($container->hasDefinition('sonata_extension_history'));
        $this->assertTrue($container->hasDefinition('sonata_extension_order'));
        $this->assertTrue($container->hasDefinition('sonata_extension_security'));
        $this->assertTrue($container->hasDefinition('sonata_extension_timestamp'));

        $this->assertTrue($container->hasDefinition('sonata_post_admin'));
        $this->assertTrue($container->hasDefinition('sonata_article_admin'));
        $this->assertTrue($container->hasDefinition('sonata_news_admin'));

        $securityExtension = $container->get('sonata_extension_security');
        $publishExtension = $container->get('sonata_extension_publish');
        $historyExtension = $container->get('sonata_extension_history');
        $orderExtension = $container->get('sonata_extension_order');
        $filterExtension = $container->get('sonata_extension_filter');

        $def = $container->get('sonata_post_admin');
        $extensions = $def->getExtensions();
        $this->assertCount(4, $extensions);

        $this->assertSame($historyExtension, $extensions[0]);
        $this->assertSame($publishExtension, $extensions[2]);
        $this->assertSame($securityExtension, $extensions[3]);

        $def = $container->get('sonata_article_admin');
        $extensions = $def->getExtensions();
        $this->assertCount(5, $extensions);

        $this->assertSame($filterExtension, $extensions[0]);
        $this->assertSame($securityExtension, $extensions[1]);
        $this->assertSame($publishExtension, $extensions[2]);
        $this->assertSame($orderExtension, $extensions[4]);

        $def = $container->get('sonata_news_admin');
        $extensions = $def->getExtensions();
        $this->assertCount(5, $extensions);
        $this->assertSame($historyExtension, $extensions[0]);
        $this->assertSame($securityExtension, $extensions[1]);
        $this->assertSame($filterExtension, $extensions[2]);
        $this->assertSame($orderExtension, $extensions[4]);
    }

    public function testProcessThrowsExceptionIfTraitsAreNotAvailable()
    {
        if (!$this->hasTraits) {
            $this->expectException('\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException', 'PHP >= 5.4.0 is required to use traits.');
        }

        $config = [
            'extensions' => [
                'sonata_extension_post' => [
                    'uses' => ['Sonata\AdminBundle\Tests\Fixtures\DependencyInjection\TimestampableTrait'],
                ],
            ],
        ];

        $container = $this->getContainer();
        $this->extension->load([$config], $container);

        $extensionsPass = new ExtensionCompilerPass();
        $extensionsPass->process($container);
        $container->compile();
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        $config = [
            'extensions' => [
                'sonata_extension_publish' => [
                    'admins' => ['sonata_post_admin'],
                    'implements' => ['Sonata\AdminBundle\Tests\DependencyInjection\Publishable'],
                ],
                'sonata_extension_history' => [
                    'excludes' => ['sonata_article_admin'],
                    'instanceof' => ['Sonata\AdminBundle\Tests\DependencyInjection\Post'],
                    'priority' => 255,
                ],
                'sonata_extension_order' => [
                    'excludes' => ['sonata_post_admin'],
                    'extends' => ['Sonata\AdminBundle\Tests\DependencyInjection\Post'],
                    'implements' => ['Sonata\AdminBundle\Tests\DependencyInjection\Publishable'],
                    'priority' => -128,
                ],
            ],
        ];

        if ($this->hasTraits) {
            $config['extensions']['sonata_extension_post']['uses'] = ['Sonata\AdminBundle\Tests\Fixtures\DependencyInjection\TimestampableTrait'];
        }

        return $config;
    }

    private function getContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', [
            'SonataCoreBundle' => true,
            'KnpMenuBundle' => true,
        ]);
        $container->setParameter('kernel.cache_dir', '/tmp');
        $container->setParameter('kernel.debug', true);

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
            ->register('property_accessor')
            ->setClass('Symfony\Component\PropertyAccess\PropertyAccessor');
        $container
            ->register('form.factory')
            ->setClass('Symfony\Component\Form\FormFactoryInterface');
        $container
            ->register('validator')
            ->setClass('Symfony\Component\Validator\ValidatorInterface');
        $container
            ->register('knp_menu.factory')
            ->setClass('Knp\Menu\FactoryInterface');
        $container
            ->register('knp_menu.matcher')
            ->setClass('Knp\Menu\Matcher\MatcherInterface');
        $container
            ->register('knp_menu.menu_provider')
            ->setClass('Knp\Menu\Provider\MenuProviderInterface');

        // Add admin definition's
        $container
            ->register('sonata_post_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(['', 'Sonata\AdminBundle\Tests\DependencyInjection\Post', 'SonataAdminBundle:CRUD'])
            ->addTag('sonata.admin');
        $container
            ->register('sonata_news_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(['', 'Sonata\AdminBundle\Tests\DependencyInjection\News', 'SonataAdminBundle:CRUD'])
            ->addTag('sonata.admin');
        $container
            ->register('sonata_article_admin')
            ->setClass('Sonata\AdminBundle\Tests\DependencyInjection\MockAdmin')
            ->setArguments(['', 'Sonata\AdminBundle\Tests\DependencyInjection\Article', 'SonataAdminBundle:CRUD'])
            ->addTag('sonata.admin');
        $container
            ->register('event_dispatcher')
            ->setClass('Symfony\Component\EventDispatcher\EventDispatcher');

        // Add admin extension definition's
        $extensionClass = get_class($this->createMock('Sonata\AdminBundle\Admin\AdminExtensionInterface'));

        $container
            ->register('sonata_extension_publish')
            ->setClass($extensionClass);
        $container
            ->register('sonata_extension_history')
            ->setClass($extensionClass);
        $container
            ->register('sonata_extension_order')
            ->setClass($extensionClass);
        $container
            ->register('sonata_extension_timestamp')
            ->setClass($extensionClass);
        $container
            ->register('sonata_extension_security')
            ->setClass($extensionClass)
            ->addTag('sonata.admin.extension', ['global' => true]);
        $container
            ->register('sonata_extension_filter')
            ->setClass($extensionClass)
            ->addTag('sonata.admin.extension', ['target' => 'sonata_news_admin'])
            ->addTag('sonata.admin.extension', ['target' => 'sonata_article_admin']);

        return $container;
    }
}

class MockAdmin extends AbstractAdmin
{
}

class MockAbstractServiceAdmin extends AbstractAdmin
{
    private $extraArgument;

    public function __construct($code, $class, $baseControllerName, $extraArgument)
    {
        $this->extraArgument = $extraArgument;

        parent::__construct($code, $class, $baseControllerName);
    }
}

class Post
{
}
interface Publishable
{
}
class News extends Post
{
}
class Article implements Publishable
{
}
