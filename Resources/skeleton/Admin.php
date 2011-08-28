<?php
namespace {{ namespace }}\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class {{ admin }} extends Admin
{
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper

        {%- for field in fields_show %}

            ->add('{{ field.name }}', {{ field.type }}, {{ field.options }})

        {%- endfor %}

        ;
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper

        {%- for field in fields_form %}

            ->add('{{ field.name }}', {{ field.type }}, {{ field.options }}, {{ field.description_options }})

        {%- endfor %}

        ;
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper

        {%- for field in fields_list %}

            ->add('{{ field.name }}', {{ field.type }}, {{ field.options }})

        {%- endfor %}

        ;
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper

        {%- for field in fields_filter %}

            ->add('{{ field.name }}', {{ field.type }}, {{ field.options }})

        {%- endfor %}

        ;
    }
}