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

namespace Sonata\AdminBundle\Tests\DependencyInjection\Compiler;

use Knp\Menu\FactoryInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminExtensionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use Sonata\AdminBundle\DependencyInjection\SonataAdminExtension;
use Sonata\BlockBundle\DependencyInjection\SonataBlockExtension;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class ExtensionCompilerPassTest extends TestCase
{
    /**
     * @var SonataAdminExtension
     */
    private $extension;

    /**
     * @var array<string, mixed>
     */
    private $config = [];

    /**
     * Root name of the configuration.
     *
     * @var string
     */
    private $root;

    protected function setUp(): void
    {
        $this->extension = new SonataAdminExtension();
        $this->config = $this->getConfig();
        $this->root = 'sonata.admin';
    }

    /**
     * @covers \Sonata\AdminBundle\DependencyInjection\SonataAdminExtension::load
     */
    public function testAdminExtensionLoad(): void
    {
        $this->extension->load([], $container = $this->getContainer());

        static::assertTrue($container->hasParameter(sprintf('%s.extension.map', $this->root)));
        static::assertIsArray($extensionMap = $container->getParameter(sprintf('%s.extension.map', $this->root)));

        static::assertSame([], $extensionMap);
    }

    /**
     * @covers \Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass::flattenExtensionConfiguration
     */
    public function testFlattenEmptyExtensionConfiguration(): void
    {
        $this->extension->load([], $container = $this->getContainer());
        $extensionMap = $container->getParameter(sprintf('%s.extension.map', $this->root));

        $method = new \ReflectionMethod(
            ExtensionCompilerPass::class,
            'flattenExtensionConfiguration'
        );

        $method->setAccessible(true);
        $extensionMap = $method->invokeArgs(new ExtensionCompilerPass(), [$extensionMap]);

        static::assertIsArray($extensionMap);
        static::assertArrayHasKey('admins', $extensionMap);
        static::assertArrayHasKey('excludes', $extensionMap);
        static::assertArrayHasKey('implements', $extensionMap);
        static::assertArrayHasKey('extends', $extensionMap);
        static::assertArrayHasKey('instanceof', $extensionMap);
        static::assertArrayHasKey('uses', $extensionMap);

        static::assertEmpty($extensionMap['global']);
        static::assertEmpty($extensionMap['admins']);
        static::assertEmpty($extensionMap['excludes']);
        static::assertEmpty($extensionMap['implements']);
        static::assertEmpty($extensionMap['extends']);
        static::assertEmpty($extensionMap['instanceof']);
        static::assertEmpty($extensionMap['uses']);
    }

    /**
     * @covers \Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass::flattenExtensionConfiguration
     */
    public function testFlattenExtensionConfiguration(): void
    {
        $config = $this->getConfig();
        $this->extension->load([$config], $container = $this->getContainer());
        $extensionMap = $container->getParameter(sprintf('%s.extension.map', $this->root));

        $method = new \ReflectionMethod(
            ExtensionCompilerPass::class,
            'flattenExtensionConfiguration'
        );

        $method->setAccessible(true);
        $extensionMap = $method->invokeArgs(new ExtensionCompilerPass(), [$extensionMap]);

        static::assertIsArray($extensionMap);

        // Admins
        static::assertArrayHasKey('admins', $extensionMap);
        static::assertCount(1, $extensionMap['admins']);

        static::assertArrayHasKey('sonata_extension_publish', $extensionMap['admins']['sonata_post_admin']);
        static::assertCount(1, $extensionMap['admins']['sonata_post_admin']);

        // Excludes
        static::assertArrayHasKey('excludes', $extensionMap);
        static::assertCount(2, $extensionMap['excludes']);

        static::assertArrayHasKey('sonata_article_admin', $extensionMap['excludes']);
        static::assertCount(1, $extensionMap['excludes']['sonata_article_admin']);
        static::assertArrayHasKey('sonata_extension_history', $extensionMap['excludes']['sonata_article_admin']);

        static::assertArrayHasKey('sonata_post_admin', $extensionMap['excludes']);
        static::assertCount(1, $extensionMap['excludes']['sonata_post_admin']);
        static::assertArrayHasKey('sonata_extension_order', $extensionMap['excludes']['sonata_post_admin']);

        // Implements
        static::assertArrayHasKey('implements', $extensionMap);
        static::assertCount(1, $extensionMap['implements']);

        static::assertArrayHasKey(Publishable::class, $extensionMap['implements']);
        static::assertCount(2, $extensionMap['implements'][Publishable::class]);
        static::assertArrayHasKey('sonata_extension_publish', $extensionMap['implements'][Publishable::class]);
        static::assertArrayHasKey('sonata_extension_order', $extensionMap['implements'][Publishable::class]);

        // Extends
        static::assertArrayHasKey('extends', $extensionMap);
        static::assertCount(1, $extensionMap['extends']);

        static::assertArrayHasKey(Post::class, $extensionMap['extends']);
        static::assertCount(1, $extensionMap['extends'][Post::class]);
        static::assertArrayHasKey('sonata_extension_order', $extensionMap['extends'][Post::class]);

        // Instanceof
        static::assertArrayHasKey('instanceof', $extensionMap);
        static::assertCount(1, $extensionMap['instanceof']);

        static::assertArrayHasKey(Post::class, $extensionMap['instanceof']);
        static::assertCount(1, $extensionMap['instanceof'][Post::class]);
        static::assertArrayHasKey('sonata_extension_history', $extensionMap['instanceof'][Post::class]);

        // Uses
        static::assertArrayHasKey('uses', $extensionMap);

        static::assertCount(1, $extensionMap['uses']);
        static::assertArrayHasKey(TimestampableTrait::class, $extensionMap['uses']);
        static::assertCount(1, $extensionMap['uses'][TimestampableTrait::class]);
        static::assertArrayHasKey('sonata_extension_post', $extensionMap['uses'][TimestampableTrait::class]);
    }

    /**
     * @covers \Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass::process
     */
    public function testProcessWithInvalidExtensionId(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $config = [
            'extensions' => [
                'sonata_extension_unknown' => [
                    'excludes' => ['sonata_article_admin'],
                    'instanceof' => [Post::class],
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
     * @doesNotPerformAssertions
     * @covers \Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass::process
     */
    public function testProcessWithInvalidAdminId(): void
    {
        $config = [
            'extensions' => [
                'sonata_extension_publish' => [
                    'admins' => ['sonata_unknown_admin'],
                    'implements' => [Publishable::class],
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
    public function testProcess(): void
    {
        $container = $this->getContainer();
        $this->extension->load([$this->config], $container);

        $extensionsPass = new ExtensionCompilerPass();
        $extensionsPass->process($container);
        $container->compile();

        static::assertTrue($container->hasDefinition('sonata_extension_global'));
        static::assertTrue($container->hasDefinition('sonata_extension_publish'));
        static::assertTrue($container->hasDefinition('sonata_extension_history'));
        static::assertTrue($container->hasDefinition('sonata_extension_order'));
        static::assertTrue($container->hasDefinition('sonata_extension_security'));
        static::assertTrue($container->hasDefinition('sonata_extension_timestamp'));

        static::assertTrue($container->hasDefinition('sonata_post_admin'));
        static::assertTrue($container->hasDefinition('sonata_article_admin'));
        static::assertTrue($container->hasDefinition('sonata_news_admin'));

        $globalExtension = $container->get('sonata_extension_global');
        $securityExtension = $container->get('sonata_extension_security');
        $publishExtension = $container->get('sonata_extension_publish');
        $historyExtension = $container->get('sonata_extension_history');
        $orderExtension = $container->get('sonata_extension_order');
        $filterExtension = $container->get('sonata_extension_filter');

        $def = $container->get('sonata_post_admin');
        static::assertInstanceOf(AdminInterface::class, $def);

        $extensions = $def->getExtensions();
        static::assertCount(5, $extensions);

        static::assertSame($historyExtension, $extensions[0]);
        static::assertSame($publishExtension, $extensions[2]);
        static::assertSame($securityExtension, $extensions[3]);
        static::assertSame($globalExtension, $extensions[4]);

        $def = $container->get('sonata_article_admin');
        static::assertInstanceOf(AdminInterface::class, $def);

        $extensions = $def->getExtensions();
        static::assertCount(6, $extensions);

        static::assertSame($filterExtension, $extensions[0]);
        static::assertSame($securityExtension, $extensions[1]);
        static::assertSame($publishExtension, $extensions[2]);
        static::assertSame($orderExtension, $extensions[4]);
        static::assertSame($globalExtension, $extensions[5]);

        $def = $container->get('sonata_news_admin');
        static::assertInstanceOf(AdminInterface::class, $def);

        $extensions = $def->getExtensions();
        static::assertCount(6, $extensions);

        static::assertSame($historyExtension, $extensions[0]);
        static::assertSame($securityExtension, $extensions[2]);
        static::assertSame($filterExtension, $extensions[3]);
        static::assertSame($orderExtension, $extensions[4]);
        static::assertSame($globalExtension, $extensions[5]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testProcessThrowsExceptionIfTraitsAreNotAvailable(): void
    {
        $config = [
            'extensions' => [
                'sonata_extension_post' => [
                    'uses' => [TimestampableTrait::class],
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
     * @return array<string, mixed>
     */
    protected function getConfig(): array
    {
        $config = [
            'extensions' => [
                'sonata_extension_global' => [
                    'global' => true,
                    'priority' => -255,
                ],
                'sonata_extension_publish' => [
                    'admins' => ['sonata_post_admin'],
                    'implements' => [Publishable::class],
                ],
                'sonata_extension_history' => [
                    'excludes' => ['sonata_article_admin'],
                    'instanceof' => [Post::class],
                    'priority' => 255,
                ],
                'sonata_extension_order' => [
                    'excludes' => ['sonata_post_admin'],
                    'extends' => [Post::class],
                    'implements' => [Publishable::class],
                    'priority' => -128,
                ],
            ],
        ];

        $config['extensions']['sonata_extension_post']['uses'] = [TimestampableTrait::class];

        return $config;
    }

    private function getContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', [
            'KnpMenuBundle' => true,
        ]);
        $container->setParameter('kernel.cache_dir', '/tmp');
        $container->setParameter('kernel.debug', true);

        // Add dependencies for SonataAdminBundle (these services will never get called so dummy classes will do)
        $container
            ->register('twig')
            ->setClass(Environment::class);
        $container
            ->register('translator')
            ->setClass(Translator::class);
        $container->setAlias(TranslatorInterface::class, 'translator');
        $container
            ->register('validator.validator_factory')
            ->setClass(ConstraintValidatorFactoryInterface::class);
        $container
            ->register('router')
            ->setClass(RouterInterface::class);
        $container
            ->register('property_accessor')
            ->setClass(PropertyAccessor::class);
        $container
            ->register('form.factory')
            ->setClass(FormFactoryInterface::class);
        $container
            ->register('validator')
            ->setClass(ValidatorInterface::class);
        $container
            ->register('knp_menu.factory')
            ->setClass(FactoryInterface::class);
        $container
            ->register('knp_menu.matcher')
            ->setClass(MatcherInterface::class);
        $container
            ->register('knp_menu.menu_provider')
            ->setClass(MenuProviderInterface::class);
        $container
            ->register('request_stack')
            ->setClass(RequestStack::class);
        $container
            ->register('session')
            ->setClass(Session::class);
        $container
            ->register('security.authorization_checker')
            ->setClass(AuthorizationCheckerInterface::class);

        // Add admin definition's
        $container
            ->register('sonata_post_admin')
            ->setPublic(true)
            ->setClass(MockAdmin::class)
            ->addTag('sonata.admin', ['model_class' => Post::class]);
        $container
            ->register('sonata_news_admin')
            ->setPublic(true)
            ->setClass(MockAdmin::class)
            ->addTag('sonata.admin', ['model_class' => News::class]);
        $container
            ->register('sonata_article_admin')
            ->setPublic(true)
            ->setClass(MockAdmin::class)
            ->addTag('sonata.admin', ['model_class' => Article::class]);
        $container
            ->register('event_dispatcher')
            ->setClass(EventDispatcher::class);

        // Add admin extension definition's
        $extensionClass = \get_class($this->createMock(AdminExtensionInterface::class));

        $container
            ->register('sonata_extension_global')
            ->setPublic(true)
            ->setClass($extensionClass);
        $container
            ->register('sonata_extension_publish')
            ->setPublic(true)
            ->setClass($extensionClass);
        $container
            ->register('sonata_extension_history')
            ->setPublic(true)
            ->setClass($extensionClass);
        $container
            ->register('sonata_extension_order')
            ->setPublic(true)
            ->setClass($extensionClass);
        $container
            ->register('sonata_extension_timestamp')
            ->setPublic(true)
            ->setClass($extensionClass);
        $container
            ->register('sonata_extension_security')
            ->setPublic(true)
            ->setClass($extensionClass)
            ->addTag('sonata.admin.extension', ['global' => true]);
        $container
            ->register('sonata_extension_filter')
            ->setPublic(true)
            ->setClass($extensionClass)
            ->addTag('sonata.admin.extension', ['target' => 'sonata_news_admin'])
            ->addTag('sonata.admin.extension', ['target' => 'sonata_article_admin']);

        // Add definitions for sonata.templating service
        $container
            ->register('kernel')
            ->setClass(KernelInterface::class);
        $container
            ->register('file_locator')
            ->setClass(FileLocatorInterface::class);

        $blockExtension = new SonataBlockExtension();
        $blockExtension->load([], $container);

        return $container;
    }
}

/** @phpstan-extends AbstractAdmin<object> */
class MockAdmin extends AbstractAdmin
{
}

trait TimestampableTrait
{
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
