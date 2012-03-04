<?php

namespace {{ namespace }}\Admin{{ entity_namespace ? '\\' ~ entity_namespace : '' }};

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Security\Acl\Permission\MaskBuilder;

class {{ form_class }} extends Admin
{
    //protected $translationDomain = '';

/*    protected $formOptions = array(
            'validation_groups' => 'Profile'
    ); */
    
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
        {% for field in fields %}
        ->add('{{ field }}')
        {% endfor %}
        ;
    }
    
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
        {% for field in fields %}
        {% if loop.first == true %}
        ->addIdentifier('{{ field }}')
        {% else %}
        ->add('{{ field }}')
        {% endif %}
        {% endfor %}
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filterMapper)
    {
        $filterMapper
        {% for field in fields %}
        ->add('{{ field }}')
        {% endfor %}
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
        ->with('General')
        {% for field in fields %}
        ->add('{{ field }}')
        {% endfor %}
        ->end()
        ;
    }
}