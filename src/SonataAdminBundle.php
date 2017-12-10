<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle;

use Mopa\Bundle\BootstrapBundle\Form\Type\TabType;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeType;
use Sonata\AdminBundle\Form\Type\Filter\DateType;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelHiddenType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelReferenceType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\CoreBundle\Form\FormHelper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SonataAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddDependencyCallsCompilerPass());
        $container->addCompilerPass(new AddFilterTypeCompilerPass());
        $container->addCompilerPass(new ExtensionCompilerPass());
        $container->addCompilerPass(new GlobalVariablesCompilerPass());

        $this->registerFormMapping();
    }

    public function boot()
    {
        $this->registerFormMapping();
    }

    /**
     * Register form mapping information.
     */
    public function registerFormMapping()
    {
        FormHelper::registerFormTypeMapping([
            'sonata_type_admin' => AdminType::class,
            'sonata_type_model' => ModelType::class,
            'sonata_type_model_list' => ModelListType::class,
            'sonata_type_model_reference' => ModelReferenceType::class,
            'sonata_type_model_hidden' => ModelHiddenType::class,
            'sonata_type_model_autocomplete' => ModelAutocompleteType::class,
            'sonata_type_native_collection' => CollectionType::class,
            'sonata_type_choice_field_mask' => ChoiceFieldMaskType::class,
            'sonata_type_filter_number' => NumberType::class,
            'sonata_type_filter_choice' => ChoiceType::class,
            'sonata_type_filter_default' => DefaultType::class,
            'sonata_type_filter_date' => DateType::class,
            'sonata_type_filter_date_range' => DateRangeType::class,
            'sonata_type_filter_datetime' => DateTimeType::class,
            'sonata_type_filter_datetime_range' => DateTimeRangeType::class,
            'tab' => TabType::class,
        ]);

        FormHelper::registerFormExtensionMapping('form', [
            'sonata.admin.form.extension.field',
            'mopa_bootstrap.form.type_extension.help',
            'mopa_bootstrap.form.type_extension.legend',
            'mopa_bootstrap.form.type_extension.error',
            'mopa_bootstrap.form.type_extension.widget',
            'mopa_bootstrap.form.type_extension.horizontal',
            'mopa_bootstrap.form.type_extension.widget_collection',
            'mopa_bootstrap.form.type_extension.tabbed',
        ]);

        FormHelper::registerFormExtensionMapping('choice', [
            'sonata.admin.form.extension.choice',
        ]);

        FormHelper::registerFormExtensionMapping('button', [
            'mopa_bootstrap.form.type_extension.button',
        ]);

        FormHelper::registerFormExtensionMapping('date', [
            'mopa_bootstrap.form.type_extension.date',
        ]);
    }
}
