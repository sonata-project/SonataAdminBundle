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
use Sonata\Twig\Bridge\Symfony\SonataTwigBundle;
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
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new SecurityBundle(),
            new KnpMenuBundle(),
            new SonataBlockBundle(),
            new SonataDoctrineBundle(),
            new SonataTwigBundle(),
            new SonataAdminBundle(),
        ];
    }

    public function getCacheDir(): string
    {
        return $this->getBaseDir().'cache';
    }

    public function getLogDir(): string
    {
        return $this->getBaseDir().'log';
    }

    public function getProjectDir()
    {
        return __DIR__;
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import($this->getProjectDir().'/config/routes.yml');
    }

    protected function configureContainer(ContainerBuilder $containerBuilder, LoaderInterface $loader)
    {
        $containerBuilder->loadFromExtension('framework', [
            'secret' => 'MySecret',
            'form' => ['enabled' => true],
            'session' => ['handler_id' => null, 'storage_id' => 'session.storage.mock_file', 'name' => 'MOCKSESSID'],
            'templating' => ['engine' => ['twig']],
            'test' => true,
        ]);

        $containerBuilder->loadFromExtension('security', [
            'firewalls' => ['main' => ['anonymous' => true]],
            'providers' => ['in_memory' => ['memory' => null]],
        ]);

        $loader->load($this->getProjectDir().'/config/services.yml');
    }

    private function getBaseDir(): string
    {
        return sys_get_temp_dir().'/sonata-admin-bundle/var/';
    }
}
