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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\EntityManager;
use SensioLabs\AdminBundle\FieldDescription\TypeGuesserChain;
use SensioLabs\AdminBundle\Builder\ORM\DatagridBuilder;
use SensioLabs\AdminBundle\Builder\ORM\FormContractor;
use SensioLabs\AdminBundle\Builder\ORM\ListBuilder;
use SensioLabs\AdminBundle\Builder\ORM\ShowBuilder;
use SensioLabs\AdminBundle\Exporter\ORM\DataSource;
use SensioLabs\AdminBundle\FieldDescription\ORM\FieldDescriptionFactory;
use SensioLabs\AdminBundle\FieldDescription\ORM\FilterTypeGuesser;
use SensioLabs\AdminBundle\FieldDescription\ORM\TypeGuesser;
use SensioLabs\AdminBundle\Model\ORM\ModelManager;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sensiolabs.admin.entity_manager', EntityManager::class)
            ->args([
                param('sensiolabs_doctrine_orm_admin.entity_manager'),
            ])
            ->factory([service('doctrine'), 'getManager'])

        ->set('sensiolabs.admin.manager.orm', ModelManager::class)
            ->public()
            ->tag('sensiolabs.admin.manager')
            ->args([
                service('doctrine'),
                service('property_accessor'),
            ])

        ->set('sensiolabs.admin.builder.orm_form', FormContractor::class)
            ->args([
                service('form.factory'),
                service('form.registry'),
            ])

        ->set('sensiolabs.admin.builder.orm_list', ListBuilder::class)
            ->args([
                service('sensiolabs.admin.guesser.orm_list_chain'),
                abstract_arg('list type templates'),
            ])

        ->set('sensiolabs.admin.guesser.orm_list', TypeGuesser::class)
            ->tag('sensiolabs.admin.guesser.orm_list')

        ->set('sensiolabs.admin.guesser.orm_list_chain', TypeGuesserChain::class)
            ->args([
                abstract_arg('guessers'),
            ])

        ->set('sensiolabs.admin.builder.orm_show', ShowBuilder::class)
            ->args([
                service('sensiolabs.admin.guesser.orm_show_chain'),
                abstract_arg('show type templates'),
            ])

        ->set('sensiolabs.admin.guesser.orm_show', TypeGuesser::class)
            ->tag('sensiolabs.admin.guesser.orm_show')

        ->set('sensiolabs.admin.guesser.orm_show_chain', TypeGuesserChain::class)
            ->args([
                abstract_arg('guessers'),
            ])

        ->set('sensiolabs.admin.builder.orm_datagrid', DatagridBuilder::class)
            ->args([
                service('form.factory'),
                service('sensiolabs.admin.builder.filter.factory'),
                service('sensiolabs.admin.guesser.orm_datagrid_chain'),
                param('form.type_extension.csrf.enabled'),
            ])

        ->set('sensiolabs.admin.guesser.orm_datagrid', FilterTypeGuesser::class)
            ->tag('sensiolabs.admin.guesser.orm_datagrid')

        ->set('sensiolabs.admin.guesser.orm_datagrid_chain', TypeGuesserChain::class)
            ->args([
                abstract_arg('guessers'),
            ])

        ->set('sensiolabs.admin.data_source.orm', DataSource::class)

        ->set('sensiolabs.admin.field_description_factory.orm', FieldDescriptionFactory::class)
            ->args([
                service('doctrine'),
            ]);
};
