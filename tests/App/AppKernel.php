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
use Sonata\BlockBundle\Cache\HttpCacheHandler;
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
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\StimulusBundle\StimulusBundle;

final class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new SecurityBundle(),
            new KnpMenuBundle(),
            new StimulusBundle(),
            new SonataBlockBundle(),
            new SonataDoctrineBundle(),
            new SonataAdminBundle(),
            new SonataTwigBundle(),
            new SonataFormBundle(),
        ];
    }

    public function getCacheDir(): string
    {
        return \sprintf('%scache', $this->getBaseDir());
    }

    public function getLogDir(): string
    {
        return \sprintf('%slog', $this->getBaseDir());
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(\sprintf('%s/config/routes.yml', $this->getProjectDir()));
    }

    protected function configureContainer(ContainerBuilder $containerBuilder, LoaderInterface $loader): void
    {
        $containerBuilder->loadFromExtension('framework', [
            'secret' => 'MySecret',
            'fragments' => ['enabled' => true],
            'form' => ['enabled' => true],
            'assets' => null,
            'test' => true,
            'router' => ['utf8' => true],
            'translator' => [
                'default_path' => '%kernel.project_dir%/translations',
            ],
            'http_method_override' => false,
            'session' => [
                'storage_factory_id' => 'session.storage.factory.mock_file',
            ],
        ]);

        $securityConfig = [
            'firewalls' => ['main' => []],
            'providers' => ['in_memory' => ['memory' => null]],
        ];

        // TODO: Remove if when dropping support of Symfony 5.4
        if (!class_exists(IsGranted::class)) {
            $securityConfig['enable_authenticator_manager'] = true;
        }

        $containerBuilder->loadFromExtension('security', $securityConfig);

        $containerBuilder->loadFromExtension('twig', [
            'default_path' => \sprintf('%s/templates', $this->getProjectDir()),
            'strict_variables' => true,
            'exception_controller' => null,
            'form_themes' => ['@SonataAdmin/Form/form_admin_fields.html.twig'],
        ]);

        $loader->load(\sprintf('%s/config/services.yml', $this->getProjectDir()));

        /*
         * TODO: Remove when support for SonataBlockBundle 4 is dropped.
         */
        $containerBuilder->loadFromExtension('sonata_block', class_exists(HttpCacheHandler::class) ? ['http_cache' => false] : []);
    }

    private function getBaseDir(): string
    {
        return \sprintf('%s/sonata-admin-bundle/var/', sys_get_temp_dir());
    }
}
