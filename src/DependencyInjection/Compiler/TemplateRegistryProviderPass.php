<?php

namespace Sonata\AdminBundle\DependencyInjection\Compiler;

use Sonata\AdminBundle\Templating\TemplateRegistryProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class TemplateRegistryProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $provider = $container->register(TemplateRegistryProvider::class, TemplateRegistryProvider::class);

        $registryMap = [];

        foreach ($container->findTaggedServiceIds('sonata.admin.template_registry') as $serviceId => $tagAttributes) {
            $registryMap[$tagAttributes[0]['admin_code']] = new Reference($serviceId);
        }

        $provider->addArgument(ServiceLocatorTagPass::register($container, $registryMap));
    }
}
