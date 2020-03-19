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
                        'array' => '@SonataAdmin/CRUD/list_array.html.twig',
                        'boolean' => '@SonataAdmin/CRUD/list_boolean.html.twig',
                        'date' => '@SonataAdmin/CRUD/list_date.html.twig',
                        'time' => '@SonataAdmin/CRUD/list_time.html.twig',
                        'datetime' => '@SonataAdmin/CRUD/list_datetime.html.twig',
                        'text' => '@SonataAdmin/CRUD/list_string.html.twig',
                        'textarea' => '@SonataAdmin/CRUD/list_string.html.twig',
                        'email' => '@SonataAdmin/CRUD/list_email.html.twig',
                        'trans' => '@SonataAdmin/CRUD/list_trans.html.twig',
                        'string' => '@SonataAdmin/CRUD/list_string.html.twig',
                        'smallint' => '@SonataAdmin/CRUD/list_string.html.twig',
                        'bigint' => '@SonataAdmin/CRUD/list_string.html.twig',
                        'integer' => '@SonataAdmin/CRUD/list_string.html.twig',
                        'decimal' => '@SonataAdmin/CRUD/list_string.html.twig',
                        'identifier' => '@SonataAdmin/CRUD/list_string.html.twig',
                        'currency' => '@SonataAdmin/CRUD/list_currency.html.twig',
                        'percent' => '@SonataAdmin/CRUD/list_percent.html.twig',
                        'choice' => '@SonataAdmin/CRUD/list_choice.html.twig',
                        'url' => '@SonataAdmin/CRUD/list_url.html.twig',
                        'html' => '@SonataAdmin/CRUD/list_html.html.twig',
                    ],
                    'show' => [
                        'array' => '@SonataAdmin/CRUD/show_array.html.twig',
                        'boolean' => '@SonataAdmin/CRUD/show_boolean.html.twig',
                        'date' => '@SonataAdmin/CRUD/show_date.html.twig',
                        'time' => '@SonataAdmin/CRUD/show_time.html.twig',
                        'datetime' => '@SonataAdmin/CRUD/show_datetime.html.twig',
                        'text' => '@SonataAdmin/CRUD/base_show_field.html.twig',
                        'email' => '@SonataAdmin/CRUD/show_email.html.twig',
                        'trans' => '@SonataAdmin/CRUD/show_trans.html.twig',
                        'string' => '@SonataAdmin/CRUD/base_show_field.html.twig',
                        'smallint' => '@SonataAdmin/CRUD/base_show_field.html.twig',
                        'bigint' => '@SonataAdmin/CRUD/base_show_field.html.twig',
                        'integer' => '@SonataAdmin/CRUD/base_show_field.html.twig',
                        'decimal' => '@SonataAdmin/CRUD/base_show_field.html.twig',
                        'currency' => '@SonataAdmin/CRUD/show_currency.html.twig',
                        'percent' => '@SonataAdmin/CRUD/show_percent.html.twig',
                        'choice' => '@SonataAdmin/CRUD/show_choice.html.twig',
                        'url' => '@SonataAdmin/CRUD/show_url.html.twig',
                        'html' => '@SonataAdmin/CRUD/show_html.html.twig',
                    ],
                ],
            ],
        ];

        $useIntlTemplates = $container->hasParameter('sonata.admin.configuration.use_intl_templates')
            ? $container->getParameter('sonata.admin.configuration.use_intl_templates')
            : false;

        if ($useIntlTemplates) {
            $defaultConfig['templates']['types']['list'] = array_merge($defaultConfig['templates']['types']['list'], [
                'date' => '@SonataAdmin/CRUD/Intl/list_date.html.twig',
                'datetime' => '@SonataAdmin/CRUD/Intl/list_datetime.html.twig',
                'smallint' => '@SonataAdmin/CRUD/Intl/list_decimal.html.twig',
                'bigint' => '@SonataAdmin/CRUD/Intl/list_decimal.html.twig',
                'integer' => '@SonataAdmin/CRUD/Intl/list_decimal.html.twig',
                'decimal' => '@SonataAdmin/CRUD/Intl/list_decimal.html.twig',
                'currency' => '@SonataAdmin/CRUD/Intl/list_currency.html.twig',
                'percent' => '@SonataAdmin/CRUD/Intl/list_percent.html.twig',
            ]);

            $defaultConfig['templates']['types']['show'] = array_merge($defaultConfig['templates']['types']['show'], [
                'date' => '@SonataAdmin/CRUD/Intl/show_date.html.twig',
                'datetime' => '@SonataAdmin/CRUD/Intl/show_datetime.html.twig',
                'smallint' => '@SonataAdmin/CRUD/Intl/show_decimal.html.twig',
                'bigint' => '@SonataAdmin/CRUD/Intl/show_decimal.html.twig',
                'integer' => '@SonataAdmin/CRUD/Intl/show_decimal.html.twig',
                'decimal' => '@SonataAdmin/CRUD/Intl/show_decimal.html.twig',
                'currency' => '@SonataAdmin/CRUD/Intl/show_currency.html.twig',
                'percent' => '@SonataAdmin/CRUD/Intl/show_percent.html.twig',
            ]);
        }

        if (!empty($defaultSonataDoctrineConfig)) {
            $defaultConfig = array_merge_recursive($defaultConfig, $defaultSonataDoctrineConfig);
        }

        array_unshift($configs, $defaultConfig);

        return $configs;
    }
}
