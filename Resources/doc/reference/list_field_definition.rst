List field definition
=====================

These fields are used to display the information inside the list table.

Example
-------

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\EntityAdmin;
    use Sonata\AdminBundle\Datagrid\ListMapper;

    class PostAdmin extends EntityAdmin
    {
        protected $list = array(
            'title'   => array(),
            'enabled' => array('type' => 'boolean'),
            'tags'    => array(),
            'summary' => array(),
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

Tweak it!
---------

It is possible to change the default template by setting a template key in the
definition.

- if the identifier key is set, then the field will be encapsulate by a link to
the edit action

