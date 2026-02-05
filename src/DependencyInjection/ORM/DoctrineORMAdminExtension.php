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

namespace SensioLabs\AdminBundle\DependencyInjection\ORM;

use SensioLabs\AdminBundle\DependencyInjection\AbstractSensioLabsAdminExtension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author Michael Williams <michael.williams@funsational.com>
 */
final class DoctrineORMAdminExtension extends AbstractSensioLabsAdminExtension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configs = $this->fixTemplatesConfiguration($configs, $container);

        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config/orm'));
        $loader->load('doctrine_orm.php');
        $loader->load('doctrine_orm_filter_types.php');

        $bundles = $container->getParameter('kernel.bundles');
        \assert(\is_array($bundles));

        if (isset($bundles['SimpleThingsEntityAuditBundle'])) {
            $loader->load('audit.php');

            $container->setParameter('sensiolabs_doctrine_orm_admin.audit.force', $config['audit']['force']);
        }

        if (interface_exists(ObjectIdentityInterface::class)) {
            // only load this in case the optional symfony/security-acl package is installed
            $loader->load('security.php');
        }

        $container->setParameter('sensiolabs_doctrine_orm_admin.entity_manager', $config['entity_manager']);

        $container->setParameter('sensiolabs_doctrine_orm_admin.templates', $config['templates']);

        // define the templates
        $container->getDefinition('sensiolabs.admin.builder.orm_list')
            ->replaceArgument(1, $config['templates']['types']['list']);

        $container->getDefinition('sensiolabs.admin.builder.orm_show')
            ->replaceArgument(1, $config['templates']['types']['show']);
    }
}
