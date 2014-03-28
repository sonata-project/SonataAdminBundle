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

SonataAdmin Options that may affect the list view:

.. code-block:: yaml

    sonata_admin:
        templates:
            list:                       SonataAdminBundle:CRUD:list.html.twig
            action:                     SonataAdminBundle:CRUD:action.html.twig
            select:                     SonataAdminBundle:CRUD:list__select.html.twig
            list_block:                 SonataAdminBundle:Block:block_admin_list.html.twig
            short_object_description:   SonataAdminBundle:Helper:short-object-description.html.twig
            batch:                      SonataAdminBundle:CRUD:list__batch.html.twig
            inner_list_row:             SonataAdminBundle:CRUD:list_inner_row.html.twig
            base_list_field:            SonataAdminBundle:CRUD:base_list_field.html.twig
            pager_links:                SonataAdminBundle:Pager:links.html.twig
            pager_results:              SonataAdminBundle:Pager:results.html.twig


To do:

- a note about Routes and how disabling them disables the related action
- adding custom columns

Customizing the fields displayed on the list page
-------------------------------------------------

You can customize the columns displayed on the list through the ``configureListFields`` method:

.. code-block:: php

    <?php

    // Example taken from Sonata E-Commerce Product Admin

    public function configureListFields(ListMapper $list)
    {
        $list
            // addIdentifier allows to specify that this column will provide a link to the entity's edition
            ->addIdentifier('name')

            // You may specify the field type directly as the second argument instead of in the options
            ->add('isVariation', 'boolean')

            // The type can be guessed as well
            ->add('enabled', null, array('editable' => true))

            // We can add options to the field depending on the type
            ->add('price', 'currency', array('currency' => $this->currencyDetector->getCurrency()->getLabel()))

            // Here we specify which method is used to render the label
            ->add('productCategories', null, array('associated_tostring' => 'getCategory'))
            ->add('productCollections', null, array('associated_tostring' => 'getCollection'))

            // You may also use dotted-notation to access specific properties of a relation to the entity
            ->add('image.name')

            // You may also specify the actions you want to be displayed in the list
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))

        ;
    }

Options
^^^^^^^

.. note::

    * ``(m)`` stands for mandatory
    * ``(o)`` stands for optional

- ``type`` (m): define the field type - mandatory for the field description itself but will try to detect the type automatically if not specified
- ``template`` (o): the template used to render the field
- ``name`` (o): the name used for the column's title
- ``link_parameters`` (o): add link parameter to the related Admin class when the ``Admin::generateUrl`` is called
- ``code`` (o): the method name to retrieve the related value
- ``associated_tostring`` (o): (deprecated, use associated_property option) the method to retrieve the "string" representation of the collection element.
- ``associated_property`` (o): property path to retrieve the "string" representation of the collection element.
- ``identifier`` (o): if set to true a link appear on the value to edit the element

Available types and associated options
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. note::

    ``(m)`` means that option is mandatory

+-----------+----------------+-----------------------------------------------------------------------+
| Type      | Options        | Description                                                           |
+===========+================+=======================================================================+
| actions   | actions        | List of available actions                                             |
+-----------+----------------+-----------------------------------------------------------------------+
| batch     |                | Renders a checkbox                                                    |
+-----------+----------------+-----------------------------------------------------------------------+
| select    |                | Renders a select box                                                  |
+-----------+----------------+-----------------------------------------------------------------------+
| array     |                | Displays an array                                                     |
+-----------+----------------+-----------------------------------------------------------------------+
| boolean   | ajax_hidden    | Yes/No; ajax_hidden allows to hide list field during an AJAX context. |
+-----------+----------------+-----------------------------------------------------------------------+
| boolean   | editable       | Yes/No; editable allows to edit directly from the list if authorized. |
+-----------+----------------+-----------------------------------------------------------------------+
| choice    | choices        | Possible choices                                                      |
+           +----------------+-----------------------------------------------------------------------+
|           | multiple       | Is it a multiple choice option? Defaults to false.                    |
+           +----------------+-----------------------------------------------------------------------+
|           | delimiter      | Separator of values if multiple.                                      |
+           +----------------+-----------------------------------------------------------------------+
|           | catalogue      | Translation catalogue.                                                |
+-----------+----------------+-----------------------------------------------------------------------+
| currency  | currency (m)   | A currency string (EUR or USD for instance).                          |
+-----------+----------------+-----------------------------------------------------------------------+
| date      | format         | A format understandable by Twig's ``date`` function.                  |
+-----------+----------------+-----------------------------------------------------------------------+
| datetime  | format         | A format understandable by Twig's ``date`` function.                  |
+-----------+----------------+-----------------------------------------------------------------------+
| percent   |                | Renders value as a percentage.                                        |
+-----------+----------------+-----------------------------------------------------------------------+
| string    |                | Renders a simple string.                                              |
+-----------+----------------+-----------------------------------------------------------------------+
| time      |                | Renders a datetime's time with format ``H:i:s``.                      |
+-----------+----------------+-----------------------------------------------------------------------+
| trans     | catalogue      | Translates the value with catalogue ``catalogue`` if defined.         |
+-----------+----------------+-----------------------------------------------------------------------+
| url       | url            | Adds a link with url ``url`` to the displayed value                   |
+           +----------------+-----------------------------------------------------------------------+
|           | route          | Give a route to generate the url                                      |
+           +                +                                                                       +
|           |   name         | Route name                                                            |
+           +                +                                                                       +
|           |   parameters   | Route parameters                                                      |
+           +----------------+-----------------------------------------------------------------------+
|           | hide_protocol  | Hide http:// or https:// (default false)                              |
+-----------+----------------+-----------------------------------------------------------------------+

If you have the SonataDoctrineORMAdminBundle installed, you have access to more field types, see `SonataDoctrineORMAdminBundle Documentation <http://sonata-project.org/bundles/doctrine-orm-admin/master/doc/reference/list_field_definition.html>`_.

Customizing the query used to generate the list
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


Customizing the sort order
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
