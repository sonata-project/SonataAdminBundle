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
            'title'   => array(),
            'enabled' => array('type' => 'boolean'),
            'tags'    => array(),
            'summary' => array()
        );

        protected function configureListFields(ListMapper $list) // optional
        {
            $list->get('summary')->setTemplate('NewsBundle:NewsAdmin:list_summary.twig');
        }
    }

As you can see, the filter fields are defined by overriding the ``filter_fields``
property and giving each definition an array of options. You can also customize
each field further by overriding the ``configureListFields()`` method, which
is blank in the parent class.

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

Edit and delete actions are available in default configuration. You can add your own! Default template file is :
    SonataAdminBundle:CRUD:list__action_[ACTION_NAME].html.twig
  
But you can specify yours by setup 'template' option like :
.. code-block:: php

    '_action' => array(
      'actions' => array(
        'delete' => array('template' => 'MyBundle:MyController:my_partial.html.twig'),
        'edit' => array()
      )
    )

Tweak it!
---------

It is possible to change the default template by setting a template key in the
definition.

- if the identifier key is set, then the field will be encapsulate by a link to
the edit action

