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

use SensioLabs\AdminBundle\Filter\ORM\BooleanFilter;
use SensioLabs\AdminBundle\Filter\ORM\CallbackFilter;
use SensioLabs\AdminBundle\Filter\ORM\ChoiceFilter;
use SensioLabs\AdminBundle\Filter\ORM\ClassFilter;
use SensioLabs\AdminBundle\Filter\ORM\CountFilter;
use SensioLabs\AdminBundle\Filter\ORM\DateFilter;
use SensioLabs\AdminBundle\Filter\ORM\DateRangeFilter;
use SensioLabs\AdminBundle\Filter\ORM\DateTimeFilter;
use SensioLabs\AdminBundle\Filter\ORM\DateTimeRangeFilter;
use SensioLabs\AdminBundle\Filter\ORM\EmptyFilter;
use SensioLabs\AdminBundle\Filter\ORM\ModelFilter;
use SensioLabs\AdminBundle\Filter\ORM\NullFilter;
use SensioLabs\AdminBundle\Filter\ORM\NumberFilter;
use SensioLabs\AdminBundle\Filter\ORM\StringFilter;
use SensioLabs\AdminBundle\Filter\ORM\StringListFilter;
use SensioLabs\AdminBundle\Filter\ORM\TimeFilter;
use SensioLabs\AdminBundle\Filter\ORM\UidFilter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sensiolabs.admin.orm.filter.type.boolean', BooleanFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_boolean'])

        ->set('sensiolabs.admin.orm.filter.type.callback', CallbackFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_callback'])

        ->set('sensiolabs.admin.orm.filter.type.choice', ChoiceFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_choice'])

        ->set('sensiolabs.admin.orm.filter.type.class', ClassFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_class'])

        ->set('sensiolabs.admin.orm.filter.type.count', CountFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_count'])

        ->set('sensiolabs.admin.orm.filter.type.date', DateFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_date'])

        ->set('sensiolabs.admin.orm.filter.type.date_range', DateRangeFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_date_range'])

        ->set('sensiolabs.admin.orm.filter.type.datetime', DateTimeFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_datetime'])

        ->set('sensiolabs.admin.orm.filter.type.datetime_range', DateTimeRangeFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_datetime_range'])

        ->set('sensiolabs.admin.orm.filter.type.empty', EmptyFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_empty'])

        ->set('sensiolabs.admin.orm.filter.type.model', ModelFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_model'])

        ->set('sensiolabs.admin.orm.filter.type.null', NullFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_null'])

        ->set('sensiolabs.admin.orm.filter.type.number', NumberFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_number'])

        ->set('sensiolabs.admin.orm.filter.type.string', StringFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_string'])

        ->set('sensiolabs.admin.orm.filter.type.string_list', StringListFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_string_list'])

        ->set('sensiolabs.admin.orm.filter.type.time', TimeFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_time'])

        ->set('sensiolabs.admin.orm.filter.type.uid', UidFilter::class)
            ->tag('sensiolabs.admin.filter.type', ['alias' => 'doctrine_orm_uid']);
};
