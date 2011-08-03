List field definition
=====================

These fields are used to display the information inside the list table.

Example
-------

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\AdminBundle\Show\ShowMapper;

    class PostAdmin extends Admin
    {
        protected function configureListFields(ListMapper $listMapper)
        {
            $listMapper
                ->addIdentifier('title')
                ->add('author')
                ->add('enabled')
                ->add('tags')
                ->add('commentsEnabled')

                // add custom action links
                ->add('_action', 'actions', array(
                    'actions' => array(
                        'view' => array(),
                        'edit' => array(),
                    )
                ))
            ;
        }
    }

Types available
---------------

The most important option for each field is the ``type``: The available
types include:

* boolean
* datetime
* decimal
* identifier
* integer
* many_to_one : a link will be added to the related edit action
* string
* text
* date

If no type is set, the ``Admin`` class will use the type defined in the doctrine
mapping definition.

List Actions
------------

You can set actions for the list items by adding an '_action' field in ``configureListFields``:

.. code-block:: php

    <?php
    $listMapper->add('_action', 'actions', array(
        'actions' => array(
            'view' => array(),
            'edit' => array(),
        )
    ))

Edit and delete actions are enabled in the default configuration. You can add
your own! Default template  file is: ``SonataAdminBundle:CRUD:list__action_[ACTION_NAME].html.twig``

You can specify your own by setting up the 'template' option like so:

.. code-block:: php

    <?php
    $listMapper->add('_action', 'actions', array(
        'actions' => array(
            'view' => array(),
            'edit' => array(),
            'delete' => array('template' => 'MyBundle:MyController:my_partial.html.twig'),
        )
    ))

Advance Usage
-------------

If you need a specific layout for a row cell, you can define a custom template

.. code-block:: php

    <?php
    namespace Sonata\MediaBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\AdminBundle\Show\ShowMapper;

    class MediaAdmin extends Admin
    {
        protected function configureListFields(ListMapper $listMapper)
        {
            $listMapper
                ->addIdentifier('id')
                ->add('image', 'string', array('template' => 'SonataMediaBundle:MediaAdmin:list_image.html.twig'))
                ->add('custom', 'string', array('template' => 'SonataMediaBundle:MediaAdmin:list_custom.html.twig'))
            ;
        }
    }

The related template :

.. code-block:: jinja

    {% extends 'SonataAdminBundle:CRUD:base_list_field.html.twig' %}

    {% block field%}
        <div>
            <strong>{{ object.name }}</strong> <br />
            {{ object.providername}} : {{ object.width }}x{{ object.height }} <br />
        </div>
    {% endblock %}
