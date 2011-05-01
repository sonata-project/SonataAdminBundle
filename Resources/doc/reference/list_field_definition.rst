List field definition
=====================

These fields are used to display the information inside the list table.

Example
-------

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Datagrid\ListMapper;

    class PostAdmin extends Admin
    {
        protected $list = array(
            'title' => array('identifier' => true), // add edit link
            'enabled',
            'tags',
            'summary',
        );

        protected function configureListFields(ListMapper $list) // optional
        {
            // or equivalent to :
            $list->add('title', array('identifier' => true));
            $list->add('enabled');
            $list->add('tags);
            $list->add('summary');
        }
    }

As you can see, the list fields are defined by overriding the ``list`` property
and giving each definition an array of options. You can also customize each 
field further by overriding the ``configureListFields()`` method, which is 
blank in the parent class.

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

You can set actions for each items in list by adding in $list, the '_action' field :

.. code-block:: php

    '_action' => array(
      'actions' => array(
        'delete' => array(),
        'edit' => array()
      )
    )

Edit and delete actions are available in default configuration. You can add your own! Default template 
file is : ``SonataAdminBundle:CRUD:list__action_[ACTION_NAME].html.twig``
  
But you can specify yours by setup 'template' option like :

.. code-block:: php

    '_action' => array(
      'actions' => array(
        'delete' => array('template' => 'MyBundle:MyController:my_partial.html.twig'),
        'edit' => array()
      )
    )

Advance Usage
-------------

If you need a specific layout for a row cell, you can define a custom template


.. code-block:: php

    class MediaAdmin extends Admin
    {
        protected $list = array(
            'custom' => array('template' => 'SonataMediaBundle:MediaAdmin:list_custom.html.twig', 'type' => 'string'),
            'enabled',
        )
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