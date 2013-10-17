The List View
=============

.. note::

    This document is a stub representing a new work in progress. If you're reading
    this you can help contribute, **no matter what your experience level with Sonata
    is**. Check out the `issues on Github`_ for more information about how to get involved.

This document will cover the List view which you use to browse the objects in your
system. It will cover configuration of the list itself and the filters you can use
to control what's visible.

Basic configuration
-------------------

To do:

- global (yml) options that affect the list view
- a note about Routes and how disabling them disables the related action
- using configureListFields() to set which fields to display
- setting the identifier, and the available options
- options available when adding general fields, inc custom templates
- targeting submodel fields using dot-separated notation
- adding custom columns


Customising the query used to generate the list
-----------------------------------------------

You can customize the list query thanks to the ``createQuery`` method.

.. code-block:: php

    <?php

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->andWhere(
            $query->expr()->eq($query->getRootAlias() . '.my_field', ':my_param')
        );
        $query->setParameter('my_param', 'my_value');
        return $query;
    }


Customising the sort order
--------------------------

Configure the default ordering in the list view
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Configuring the default ordering column can simply be achieved by overriding
the ``datagridValues`` array property. All three keys ``_page``, ``_sort_order`` and
``_sort_by`` can be omitted.

.. code-block:: php

    <?php

    use Sonata\AdminBundle\Admin\Admin;

    class PageAdmin extends Admin
    {
        // ...

        /**
         * Default Datagrid values
         *
         * @var array
         */
        protected $datagridValues = array(
            '_page' => 1,            // display the first page (default = 1)
            '_sort_order' => 'DESC', // reverse order (default = 'ASC')
            '_sort_by' => 'updated'  // name of the ordered field
                                     // (default = the model's id field, if any)

            // the '_sort_by' key can be of the form 'mySubModel.mySubSubModel.myField'.
        );

        // ...
    }

To do:

- how to sort by multiple fields (this might be a separate recipe?)


Filters
-------

To do:

- basic filter configuration and options
- how to set default filter values
- targeting submodel fields using dot-separated notation
- advanced filter options (global_search)

.. _`issues on Github`: https://github.com/sonata-project/SonataAdminBundle/issues/1519
