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

namespace SensioLabs\AdminBundle\Tests\App\ORM;

use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\CacheCompatibilityPass;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Knp\Bundle\MenuBundle\KnpMenuBundle;
use SensioLabs\AdminBundle\SensioLabsAdminBundle;
use Sonata\Exporter\Bridge\Symfony\SonataExporterBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\UX\StimulusBundle\StimulusBundle;

final class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        $bundles = [
            new DoctrineBundle(),
            new DAMADoctrineTestBundle(),
            new FrameworkBundle(),
            new KnpMenuBundle(),
            new SecurityBundle(),
            new SensioLabsAdminBundle(),
            new SonataExporterBundle(),
            new TwigBundle(),
            new DoctrineFixturesBundle(),
        ];

        if (class_exists(StimulusBundle::class)) {
            $bundles[] = new StimulusBundle();
        }

        return $bundles;
    }

    public function getCacheDir(): string
    {
        return $this->getBaseDir().'cache';
    }

    public function getLogDir(): string
    {
        return $this->getBaseDir().'log';
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(\sprintf('%s/config/routes.yaml', $this->getProjectDir()));
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->setParameter('app.base_dir', $this->getBaseDir());

        $loader->load(__DIR__.'/config/config.yml');

        if (\PHP_VERSION_ID >= 80400) {
            $container->loadFromExtension('doctrine', [
                'orm' => [
                    'enable_native_lazy_objects' => true,
                ],
            ]);
        }

        if (class_exists(CacheCompatibilityPass::class)) {
            // doctrine-bundle v2
            $container->loadFromExtension('doctrine', [
                'dbal' => [
                    'use_savepoints' => true,
                ],
                'orm' => [
                    'auto_generate_proxy_classes' => true,
                    'report_fields_where_declared' => true,
                ],
            ]);
        }

        $loader->load(__DIR__.'/config/services.php');
    }

    private function getBaseDir(): string
    {
        return sys_get_temp_dir().'/sensiolabs-admin-bundle-orm/var/';
    }
}
