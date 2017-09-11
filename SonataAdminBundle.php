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

use Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Sonata\CoreBundle\Form\FormHelper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SonataAdminBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddDependencyCallsCompilerPass());
        $container->addCompilerPass(new AddFilterTypeCompilerPass());
        $container->addCompilerPass(new ExtensionCompilerPass());
        $container->addCompilerPass(new GlobalVariablesCompilerPass());

        $this->registerFormMapping();
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->registerFormMapping();
    }

    /**
     * Register form mapping information.
     */
    public function registerFormMapping()
    {
        FormHelper::registerFormTypeMapping(array(
            'sonata_type_admin' => 'Sonata\AdminBundle\Form\Type\AdminType',
            'sonata_type_model' => 'Sonata\AdminBundle\Form\Type\ModelType',
            'sonata_type_model_list' => 'Sonata\AdminBundle\Form\Type\ModelListType',
            'sonata_type_model_reference' => 'Sonata\AdminBundle\Form\Type\ModelReferenceType',
            'sonata_type_model_hidden' => 'Sonata\AdminBundle\Form\Type\ModelHiddenType',
            'sonata_type_model_autocomplete' => 'Sonata\AdminBundle\Form\Type\ModelAutocompleteType',
            'sonata_type_native_collection' => 'Sonata\AdminBundle\Form\Type\CollectionType',
            'sonata_type_choice_field_mask' => 'Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType',
            'sonata_type_filter_number' => 'Sonata\AdminBundle\Form\Type\Filter\NumberType',
            'sonata_type_filter_choice' => 'Sonata\AdminBundle\Form\Type\Filter\ChoiceType',
            'sonata_type_filter_default' => 'Sonata\AdminBundle\Form\Type\Filter\DefaultType',
            'sonata_type_filter_date' => 'Sonata\AdminBundle\Form\Type\Filter\DateType',
            'sonata_type_filter_date_range' => 'Sonata\AdminBundle\Form\Type\Filter\DateRangeType',
            'sonata_type_filter_datetime' => 'Sonata\AdminBundle\Form\Type\Filter\DateTimeType',
            'sonata_type_filter_datetime_range' => 'Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType',
            'tab' => 'Mopa\Bundle\BootstrapBundle\Form\Type\TabType',
        ));

        FormHelper::registerFormExtensionMapping('form', array(
            'sonata.admin.form.extension.field',
            'mopa_bootstrap.form.type_extension.help',
            'mopa_bootstrap.form.type_extension.legend',
            'mopa_bootstrap.form.type_extension.error',
            'mopa_bootstrap.form.type_extension.widget',
            'mopa_bootstrap.form.type_extension.horizontal',
            'mopa_bootstrap.form.type_extension.widget_collection',
            'mopa_bootstrap.form.type_extension.tabbed',
        ));

        FormHelper::registerFormExtensionMapping('choice', array(
            'sonata.admin.form.extension.choice',
        ));

        FormHelper::registerFormExtensionMapping('button', array(
            'mopa_bootstrap.form.type_extension.button',
        ));

        FormHelper::registerFormExtensionMapping('date', array(
            'mopa_bootstrap.form.type_extension.date',
        ));
    }
}
