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
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
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
        $routes->import(\sprintf('%s/config/routes.php', $this->getProjectDir()), 'php');
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
            'http_method_override' => false,
            'session' => [
                'storage_factory_id' => 'session.storage.factory.mock_file',
            ],
        ];

        // TODO: remove condition and always set option once Support for Symfony 6.4 is dropped
        /* @phpstan-ignore function.alreadyNarrowedType */
        if (method_exists(PropertyTypeExtractorInterface::class, 'getType')) {
            $frameworkConfig['property_info']['with_constructor_extractor'] = true;
        }

        $containerBuilder->loadFromExtension('framework', $frameworkConfig);

        $containerBuilder->loadFromExtension('security', [
            'firewalls' => ['main' => []],
            'providers' => ['in_memory' => ['memory' => null]],
        ]);

        $containerBuilder->loadFromExtension('twig', [
            'default_path' => \sprintf('%s/templates', $this->getProjectDir()),
            'strict_variables' => true,
            'form_themes' => ['@SonataAdmin/Form/form_admin_fields.html.twig'],
        ]);

        $loader->load(\sprintf('%s/config/services.yml', $this->getProjectDir()));
    }

    private function getBaseDir(): string
    {
        return \sprintf('%s/sonata-admin-bundle/var/', sys_get_temp_dir());
    }
}
