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

namespace Sonata\AdminBundle\Tests\App;

use Knp\Bundle\MenuBundle\KnpMenuBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Sonata\AdminBundle\SonataAdminBundle;
use Sonata\BlockBundle\SonataBlockBundle;
use Sonata\Doctrine\Bridge\Symfony\SonataDoctrineBundle;
use Sonata\Form\Bridge\Symfony\SonataFormBundle;
use Sonata\Twig\Bridge\Symfony\SonataTwigBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorageFactory;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\Component\Security\Http\Authentication\AuthenticatorManager;

final class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', false);
    }

    public function registerBundles()
    {
        $bundles = [
            new FrameworkBundle(),
            new SensioFrameworkExtraBundle(),
            new TwigBundle(),
            new SecurityBundle(),
            new KnpMenuBundle(),
            new SonataBlockBundle(),
            new SonataDoctrineBundle(),
            new SonataAdminBundle(),
            new SonataTwigBundle(),
            new SonataFormBundle(),
        ];

        return $bundles;
    }

    public function getCacheDir(): string
    {
        return sprintf('%scache', $this->getBaseDir());
    }

    public function getLogDir(): string
    {
        return sprintf('%slog', $this->getBaseDir());
    }

    public function getProjectDir()
    {
        return __DIR__;
    }

    /**
     * TODO: Drop RouteCollectionBuilder when support for Symfony < 5.1 is dropped.
     *
     * @param RoutingConfigurator|RouteCollectionBuilder $routes
     */
    protected function configureRoutes($routes): void
    {
        $routes->import(sprintf('%s/config/routes.yml', $this->getProjectDir()));
    }

    protected function configureContainer(ContainerBuilder $containerBuilder, LoaderInterface $loader): void
    {
        $frameworkConfig = [
            'secret' => 'MySecret',
            'fragments' => ['enabled' => true],
            'form' => ['enabled' => true],
            'assets' => null,
            'test' => true,
            'router' => ['utf8' => true],
            'translator' => [
                'default_path' => '%kernel.project_dir%/translations',
            ],
        ];

        // TODO: Remove else case when dropping support of Symfony < 5.3
        if (class_exists(NativeSessionStorageFactory::class)) {
            $frameworkConfig['session'] = ['storage_factory_id' => 'session.storage.factory.mock_file'];
        } else {
            $frameworkConfig['session'] = ['storage_id' => 'session.storage.mock_file'];
        }

        $containerBuilder->loadFromExtension('framework', $frameworkConfig);

        $securityConfig = [
            'firewalls' => ['main' => []],
            'providers' => ['in_memory' => ['memory' => null]],
        ];

        // TODO: Remove else case when dropping support of Symfony < 5.3
        if (class_exists(AuthenticatorManager::class)) {
            $securityConfig['enable_authenticator_manager'] = true;
        } else {
            $securityConfig['firewalls']['main']['anonymous'] = true;
        }

        $containerBuilder->loadFromExtension('security', $securityConfig);

        $containerBuilder->loadFromExtension('twig', [
            'strict_variables' => '%kernel.debug%',
            'exception_controller' => null,
            'form_themes' => ['@SonataAdmin/Form/form_admin_fields.html.twig'],
        ]);

        $containerBuilder->loadFromExtension('sensio_framework_extra', [
            'router' => ['annotations' => false],
        ]);

        $loader->load(sprintf('%s/config/services.yml', $this->getProjectDir()));
    }

    private function getBaseDir(): string
    {
        return sprintf('%s/sonata-admin-bundle/var/', sys_get_temp_dir());
    }
}
