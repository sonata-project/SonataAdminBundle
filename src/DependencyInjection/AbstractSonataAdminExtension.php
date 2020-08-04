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

use Sonata\AdminBundle\Templating\TemplateRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class AbstractSonataAdminExtension extends Extension
{
    /**
     * @return array
     */
    protected function fixTemplatesConfiguration(
        array $configs,
        ContainerBuilder $container,
        array $defaultSonataDoctrineConfig = []
    ) {
        $defaultConfig = [
            'templates' => [
                'types' => [
                    'list' => [
                        TemplateRegistry::TYPE_ARRAY => '@SonataAdmin/CRUD/list_array.html.twig',
                        TemplateRegistry::TYPE_BOOLEAN => '@SonataAdmin/CRUD/list_boolean.html.twig',
                        TemplateRegistry::TYPE_DATE => '@SonataAdmin/CRUD/list_date.html.twig',
                        TemplateRegistry::TYPE_TIME => '@SonataAdmin/CRUD/list_time.html.twig',
                        TemplateRegistry::TYPE_DATETIME => '@SonataAdmin/CRUD/list_datetime.html.twig',
                        TemplateRegistry::TYPE_TEXTAREA => '@SonataAdmin/CRUD/list_string.html.twig',
                        TemplateRegistry::TYPE_EMAIL => '@SonataAdmin/CRUD/list_email.html.twig',
                        TemplateRegistry::TYPE_TRANS => '@SonataAdmin/CRUD/list_trans.html.twig',
                        TemplateRegistry::TYPE_STRING => '@SonataAdmin/CRUD/list_string.html.twig',
                        TemplateRegistry::TYPE_INTEGER => '@SonataAdmin/CRUD/list_string.html.twig',
                        TemplateRegistry::TYPE_FLOAT => '@SonataAdmin/CRUD/list_string.html.twig',
                        TemplateRegistry::TYPE_IDENTIFIER => '@SonataAdmin/CRUD/list_string.html.twig',
                        TemplateRegistry::TYPE_CURRENCY => '@SonataAdmin/CRUD/list_currency.html.twig',
                        TemplateRegistry::TYPE_PERCENT => '@SonataAdmin/CRUD/list_percent.html.twig',
                        TemplateRegistry::TYPE_CHOICE => '@SonataAdmin/CRUD/list_choice.html.twig',
                        TemplateRegistry::TYPE_URL => '@SonataAdmin/CRUD/list_url.html.twig',
                        TemplateRegistry::TYPE_HTML => '@SonataAdmin/CRUD/list_html.html.twig',
                        TemplateRegistry::TYPE_MANY_TO_MANY => '@SonataAdmin/CRUD/Association/list_many_to_many.html.twig',
                        TemplateRegistry::TYPE_MANY_TO_ONE => '@SonataAdmin/CRUD/Association/list_many_to_one.html.twig',
                        TemplateRegistry::TYPE_ONE_TO_MANY => '@SonataAdmin/CRUD/Association/list_one_to_many.html.twig',
                        TemplateRegistry::TYPE_ONE_TO_ONE => '@SonataAdmin/CRUD/Association/list_one_to_one.html.twig',
                    ],
                    'show' => [
                        TemplateRegistry::TYPE_ARRAY => '@SonataAdmin/CRUD/show_array.html.twig',
                        TemplateRegistry::TYPE_BOOLEAN => '@SonataAdmin/CRUD/show_boolean.html.twig',
                        TemplateRegistry::TYPE_DATE => '@SonataAdmin/CRUD/show_date.html.twig',
                        TemplateRegistry::TYPE_TIME => '@SonataAdmin/CRUD/show_time.html.twig',
                        TemplateRegistry::TYPE_DATETIME => '@SonataAdmin/CRUD/show_datetime.html.twig',
                        TemplateRegistry::TYPE_EMAIL => '@SonataAdmin/CRUD/show_email.html.twig',
                        TemplateRegistry::TYPE_TRANS => '@SonataAdmin/CRUD/show_trans.html.twig',
                        TemplateRegistry::TYPE_STRING => '@SonataAdmin/CRUD/base_show_field.html.twig',
                        TemplateRegistry::TYPE_INTEGER => '@SonataAdmin/CRUD/base_show_field.html.twig',
                        TemplateRegistry::TYPE_FLOAT => '@SonataAdmin/CRUD/base_show_field.html.twig',
                        TemplateRegistry::TYPE_CURRENCY => '@SonataAdmin/CRUD/show_currency.html.twig',
                        TemplateRegistry::TYPE_PERCENT => '@SonataAdmin/CRUD/show_percent.html.twig',
                        TemplateRegistry::TYPE_CHOICE => '@SonataAdmin/CRUD/show_choice.html.twig',
                        TemplateRegistry::TYPE_URL => '@SonataAdmin/CRUD/show_url.html.twig',
                        TemplateRegistry::TYPE_HTML => '@SonataAdmin/CRUD/show_html.html.twig',
                        TemplateRegistry::TYPE_MANY_TO_MANY => '@SonataAdmin/CRUD/Association/show_many_to_many.html.twig',
                        TemplateRegistry::TYPE_MANY_TO_ONE => '@SonataAdmin/CRUD/Association/show_many_to_one.html.twig',
                        TemplateRegistry::TYPE_ONE_TO_MANY => '@SonataAdmin/CRUD/Association/show_one_to_many.html.twig',
                        TemplateRegistry::TYPE_ONE_TO_ONE => '@SonataAdmin/CRUD/Association/show_one_to_one.html.twig',
                    ],
                ],
            ],
        ];

        // let's add some magic, only overwrite template if the SonataIntlBundle is enabled
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['SonataIntlBundle'])) {
            $defaultConfig['templates']['types']['list'] = array_merge($defaultConfig['templates']['types']['list'], [
                TemplateRegistry::TYPE_DATE => '@SonataIntl/CRUD/list_date.html.twig',
                TemplateRegistry::TYPE_DATETIME => '@SonataIntl/CRUD/list_datetime.html.twig',
                TemplateRegistry::TYPE_INTEGER => '@SonataIntl/CRUD/list_decimal.html.twig',
                TemplateRegistry::TYPE_FLOAT => '@SonataIntl/CRUD/list_decimal.html.twig',
                TemplateRegistry::TYPE_CURRENCY => '@SonataIntl/CRUD/list_currency.html.twig',
                TemplateRegistry::TYPE_PERCENT => '@SonataIntl/CRUD/list_percent.html.twig',
            ]);

            $defaultConfig['templates']['types']['show'] = array_merge($defaultConfig['templates']['types']['show'], [
                TemplateRegistry::TYPE_DATE => '@SonataIntl/CRUD/show_date.html.twig',
                TemplateRegistry::TYPE_DATETIME => '@SonataIntl/CRUD/show_datetime.html.twig',
                TemplateRegistry::TYPE_INTEGER => '@SonataIntl/CRUD/show_decimal.html.twig',
                TemplateRegistry::TYPE_FLOAT => '@SonataIntl/CRUD/list_decimal.html.twig',
                TemplateRegistry::TYPE_CURRENCY => '@SonataIntl/CRUD/show_currency.html.twig',
                TemplateRegistry::TYPE_PERCENT => '@SonataIntl/CRUD/show_percent.html.twig',
            ]);
        }

        if (!empty($defaultSonataDoctrineConfig)) {
            $defaultConfig = array_merge_recursive($defaultConfig, $defaultSonataDoctrineConfig);
        }

        array_unshift($configs, $defaultConfig);

        return $configs;
    }
}
