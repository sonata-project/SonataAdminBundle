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

namespace Sonata\AdminBundle\DependencyInjection\Compiler;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Templating\MutableTemplateRegistry;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryAwareInterface;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Sonata\AdminBundle\Templating\TemplateRegistryAwareInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Wojciech Błoszyk <wbloszyk@gmail.com>
 */
final class TemplateRegistryCompilerPass implements CompilerPassInterface
{
    private const TAG = 'sonata.admin.template_registry';

    public function process(ContainerBuilder $container): void
    {
        $taggedServices = $container->findTaggedServiceIds(self::TAG);

        foreach ($taggedServices as $id => $tags) {
            $templates = [];

            foreach ($tags as $attributes) {
                if (isset($attributes['template_name'], $attributes['template_path'])) {
                    $templates[$attributes['template_name']] = $attributes['template_path'];
                }
            }

            if (empty($templates)) {
                continue;
            }

            $definition = $container->getDefinition($id);

            if (is_a($definition->getClass(), MutableTemplateRegistryInterface::class, true)) {
                $this->setTemplatesByMethod($definition, $templates, $definition);
            } elseif (is_a($definition->getClass(), TemplateRegistryInterface::class, true)) {
                $this->setTemplatesByArgument($container, $definition, $templates);
            } elseif (is_a($definition->getClass(), MutableTemplateRegistryAwareInterface::class, true)) {
                $templateRegistryDefinition = $this->getTemplateRegistryDefinition($container, $id);
                if (null === $templateRegistryDefinition) {
                    $templateRegistryDefinition = $this->generateTemplateRegistry($container, $definition, $id);
                }
                $this->setTemplatesByMethod($templateRegistryDefinition, $templates, $definition);
            } elseif (is_a($definition->getClass(), TemplateRegistryAwareInterface::class, true)) {
                $templateRegistryDefinition = $this->getTemplateRegistryDefinition($container, $id);
                if (null !== $templateRegistryDefinition) {
                    throw new \Exception('You cannot set templates where non mutable template registry is defined.');
                }
                $templateRegistryDefinition = $this->generateTemplateRegistry($container, $definition, $id);
                $this->setTemplatesByArgument($container, $templateRegistryDefinition, $templates);
            } else {
                throw new \Exception(sprintf(
                    'Tagged "%s" service must be instance of %s or %s or %s or %s.',
                    $id,
                    TemplateRegistryInterface::class,
                    MutableTemplateRegistryInterface::class,
                    TemplateRegistryAwareInterface::class,
                    MutableTemplateRegistryAwareInterface::class
                ));
            }
        }
    }

    /**
     * @param array<string, string> $templates
     */
    private function setTemplatesByMethod(Definition $templateRegistryDefinition, array $templates, ?Definition $awareDefinition = null): void
    {
        foreach ($templates as $type => $template) {
            // NEXT_MAJOR: remove this if
            // support for deprecated `AbstractAdmin:setTemplate()` method to keep BC
            if (null !== $awareDefinition && is_a($awareDefinition->getClass(), AbstractAdmin::class, true)) {
                $awareDefinition->addMethodCall('setTemplate', [$type, $template]);

                continue;
            }

            $templateRegistryDefinition->addMethodCall('setTemplate', [$type, $template]);
        }
    }

    /**
     * @param array<string, string> $templates
     */
    private function setTemplatesByArgument(ContainerBuilder $container, Definition $templateRegistryDefinition, array $templates): void
    {
        $arguments = $templateRegistryDefinition->getArguments();
        $serviceTemplates = $arguments[0] ?? [];

        if (\is_string($serviceTemplates)) {
            $serviceTemplates = $container->getParameter($serviceTemplates);
        }

        $templateRegistryDefinition->setArgument(0, array_merge($serviceTemplates, $templates));
    }

    private function getTemplateRegistryDefinition(ContainerBuilder $container, string $id): ?Definition
    {
        $definition = $container->getDefinition($id);

        if (is_a($definition->getClass(), TemplateRegistryInterface::class, true)) {
            return $definition;
        }

        if ($templateRegistryDefinition = $this->getTemplateRegistryFromAwareDefinition($container, $definition, $id)) {
            return $templateRegistryDefinition;
        }

        return null;
    }

    /**
     * @phpstan-return class-string
     */
    private function getTemplateRegistryInterface(Definition $awareDefinition, string $id): string
    {
        $templateRegistryClass = null;
        if (is_a($awareDefinition->getClass(), MutableTemplateRegistryAwareInterface::class, true)) {
            $templateRegistryClass = MutableTemplateRegistryInterface::class;
        } elseif (is_a($awareDefinition->getClass(), TemplateRegistryAwareInterface::class, true)) {
            $templateRegistryClass = TemplateRegistryInterface::class;
        }

        if (null === $templateRegistryClass) {
            throw new \Exception(sprintf(
                'Tagged "%s" service must be instance of %s or %s.',
                $id,
                TemplateRegistryAwareInterface::class,
                MutableTemplateRegistryAwareInterface::class
            ));
        }

        return $templateRegistryClass;
    }

    private function getTemplateRegistryFromAwareDefinition(ContainerBuilder $container, Definition $awareDefinition, string $id): ?Definition
    {
        $templateRegistryInterface = $this->getTemplateRegistryInterface($awareDefinition, $id);

        foreach ($awareDefinition->getMethodCalls() as [$method, $args]) {
            if ('setTemplateRegistry' === $method) {
                $reference = $args[0];
                $templateRegistryDefinition = $container->getDefinition($reference->__toString());

                if (!is_a($templateRegistryDefinition->getClass(), $templateRegistryInterface, true)) {
                    throw new \Exception(sprintf(
                        'Argument 1 passed to "setTemplateRegistry()" call in service "%s" MUST be an instance of "%s", instance of "%s" given.',
                        $id,
                        $templateRegistryInterface,
                        $templateRegistryDefinition->getClass()
                    ));
                }

                return $templateRegistryDefinition;
            }
        }

        return null;
    }

    private function generateTemplateRegistry(ContainerBuilder $container, Definition $awareDefinition, string $id): Definition
    {
        $templateRegistryInterface = $this->getTemplateRegistryInterface($awareDefinition, $id);
        $templateRegistryClass = TemplateRegistry::class;

        if (MutableTemplateRegistryInterface::class === $templateRegistryInterface) {
            $templateRegistryClass = MutableTemplateRegistry::class;
        }

        $templateRegistryId = sprintf('%s.template_registry', $id);
        $templateRegistryDefinition = $container
            ->register($templateRegistryId, $templateRegistryClass)
            ->addTag('sonata.admin.template_registry')
            ->setPublic(true);

        $awareDefinition->addMethodCall('setTemplateRegistry', [new Reference($templateRegistryId)]);

        return $templateRegistryDefinition;
    }
}
