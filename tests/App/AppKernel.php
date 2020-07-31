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
use Sonata\AdminBundle\SonataAdminBundle;
use Sonata\BlockBundle\SonataBlockBundle;
use Sonata\Doctrine\Bridge\Symfony\Bundle\SonataDoctrineBundle;
use Sonata\Form\Bridge\Symfony\SonataFormBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

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
            new TwigBundle(),
            new SecurityBundle(),
            new KnpMenuBundle(),
            new SonataBlockBundle(),
            new SonataDoctrineBundle(),
            new SonataAdminBundle(),
            new SonataTwigBundle(),
            new SonataFormBundle(),
        ];

        if (class_exists(SonataCoreBundle::class)) {
            $bundles[] = new SonataCoreBundle();
        }

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

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import(sprintf('%s/config/routes.yml', $this->getProjectDir()));
    }

    protected function configureContainer(ContainerBuilder $containerBuilder, LoaderInterface $loader)
    {
        $containerBuilder->loadFromExtension('framework', [
            'secret' => 'MySecret',
            'fragments' => ['enabled' => true],
            'form' => ['enabled' => true],
            'session' => ['handler_id' => 'session.handler.native_file', 'storage_id' => 'session.storage.mock_file', 'name' => 'MOCKSESSID'],
            'assets' => null,
            'test' => true,
            'translator' => [
                'default_path' => '%kernel.project_dir%/translations',
            ],
        ]);

        $containerBuilder->loadFromExtension('security', [
            'firewalls' => ['main' => ['anonymous' => true]],
            'providers' => ['in_memory' => ['memory' => null]],
        ]);

        $containerBuilder->loadFromExtension('twig', [
            'strict_variables' => '%kernel.debug%',
            'exception_controller' => null,
        ]);

        $loader->load(sprintf('%s/config/services.yml', $this->getProjectDir()));
    }

    private function getBaseDir(): string
    {
        return sprintf('%s/sonata-admin-bundle/var/', sys_get_temp_dir());
    }
}
