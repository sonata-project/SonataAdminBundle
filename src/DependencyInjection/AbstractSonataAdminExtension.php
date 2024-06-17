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

namespace Sonata\AdminBundle\DependencyInjection;

use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class AbstractSonataAdminExtension extends Extension
{
    /**
     * @param array<string, mixed> $configs
     * @param array<string, mixed> $defaultSonataDoctrineConfig
     *
     * @return mixed[]
     */
    protected function fixTemplatesConfiguration(
        array $configs,
        ContainerBuilder $container,
        array $defaultSonataDoctrineConfig = []
    ): array {
        $defaultConfig = [
            'templates' => [
                'types' => [
                    'list' => TemplateRegistryInterface::LIST_TEMPLATES,
                    'show' => TemplateRegistryInterface::SHOW_TEMPLATES,
                ],
            ],
        ];

        // let's add some magic, only overwrite template if the SonataIntlBundle is enabled
        $bundles = $container->getParameter('kernel.bundles');
        \assert(\is_array($bundles));

        if (isset($bundles['SonataIntlBundle'])) {
            $defaultConfig['templates']['types']['list'] = array_merge($defaultConfig['templates']['types']['list'], [
                FieldDescriptionInterface::TYPE_DATE => '@SonataIntl/CRUD/list_date.html.twig',
                FieldDescriptionInterface::TYPE_DATETIME => '@SonataIntl/CRUD/list_datetime.html.twig',
                FieldDescriptionInterface::TYPE_INTEGER => '@SonataIntl/CRUD/list_decimal.html.twig',
                FieldDescriptionInterface::TYPE_FLOAT => '@SonataIntl/CRUD/list_decimal.html.twig',
                FieldDescriptionInterface::TYPE_CURRENCY => '@SonataIntl/CRUD/list_currency.html.twig',
                FieldDescriptionInterface::TYPE_PERCENT => '@SonataIntl/CRUD/list_percent.html.twig',
            ]);

            $defaultConfig['templates']['types']['show'] = array_merge($defaultConfig['templates']['types']['show'], [
                FieldDescriptionInterface::TYPE_DATE => '@SonataIntl/CRUD/show_date.html.twig',
                FieldDescriptionInterface::TYPE_DATETIME => '@SonataIntl/CRUD/show_datetime.html.twig',
                FieldDescriptionInterface::TYPE_INTEGER => '@SonataIntl/CRUD/show_decimal.html.twig',
                FieldDescriptionInterface::TYPE_FLOAT => '@SonataIntl/CRUD/show_decimal.html.twig',
                FieldDescriptionInterface::TYPE_CURRENCY => '@SonataIntl/CRUD/show_currency.html.twig',
                FieldDescriptionInterface::TYPE_PERCENT => '@SonataIntl/CRUD/show_percent.html.twig',
            ]);
        }

        if ([] !== $defaultSonataDoctrineConfig) {
            $defaultConfig = array_merge_recursive($defaultConfig, $defaultSonataDoctrineConfig);
        }

        array_unshift($configs, $defaultConfig);

        return $configs;
    }
}
