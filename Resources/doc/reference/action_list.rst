The List View
=============

.. note::

    This document is a stub representing a new work in progress. If you're reading
    this you can help contribute, **no matter what your experience level with Sonata
    is**. Check out the `issues on GitHub`_ for more information about how to get involved.

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


.. note::

    **TODO**:
    * a note about Routes and how disabling them disables the related action
    * adding custom columns

Customizing the fields displayed on the list page
-------------------------------------------------

You can customize the columns displayed on the list through the ``configureListFields`` method.
Here is an example:

.. code-block:: php

    <?php

    // ...

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            // addIdentifier allows to specify that this column
            // will provide a link to the entity
            // (edit or show route, depends on your access rights)
            ->addIdentifier('name')

            // you may specify the field type directly as the
            // second argument instead of in the options
            ->add('isVariation', 'boolean')

            // if null, the type will be guessed
            ->add('enabled', null, array(
                'editable' => true
            ))

            // we can add options to the field depending on the type
            ->add('price', 'currency', array(
                'currency' => $this->currencyDetector->getCurrency()->getLabel()
            ))

            // Here we specify which property is used to render the label of each entity in the list
            ->add('productCategories', null, array(
                'associated_property' => 'name')
            )

            // you may also use dotted-notation to access
            // specific properties of a relation to the entity
            ->add('image.name')

            // You may also specify the actions you want to be displayed in the list
            ->add('_action', null, array(
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

- ``type`` (m): defines the field type - mandatory for the field description itself but will try to detect the type automatically if not specified
- ``template`` (o): the template used to render the field
- ``label`` (o): the name used for the column's title
- ``link_parameters`` (o): add link parameter to the related Admin class when the ``Admin::generateUrl`` is called
- ``code`` (o): the method name to retrieve the related value (for example,
  if you have an `array` type field, you would like to show info prettier
  than `[0] => 'Value'`; useful when simple getter is not enough).
  Notice: works with string-like types (string, text, html)
- ``associated_property`` (o): property path to retrieve the "string" representation of the collection element, or a closure with the element as argument and return a string.
- ``identifier`` (o): if set to true a link appears on the value to edit the element

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
| email     | as_string      | Renders the email as string, without any link.                        |
+           +----------------+-----------------------------------------------------------------------+
|           | subject        | Add subject parameter to email link.                                  |
+           +----------------+-----------------------------------------------------------------------+
|           | body           | Add body parameter to email link.                                     |
+-----------+----------------+-----------------------------------------------------------------------+
| percent   |                | Renders value as a percentage.                                        |
+-----------+----------------+-----------------------------------------------------------------------+
| string    |                | Renders a simple string.                                              |
+-----------+----------------+-----------------------------------------------------------------------+
| text      |                | See 'string'                                                          |
+-----------+----------------+-----------------------------------------------------------------------+
| html      |                | Renders string as html                                                |
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
|           | hide_protocol  | Hide http:// or https:// (default: false)                             |
+-----------+----------------+-----------------------------------------------------------------------+

If you have the SonataDoctrineORMAdminBundle installed, you have access to more field types, see `SonataDoctrineORMAdminBundle Documentation <https://sonata-project.org/bundles/doctrine-orm-admin/master/doc/reference/list_field_definition.html>`_.

Customizing the query used to generate the list
-----------------------------------------------

You can customize the list query thanks to the ``createQuery`` method.

.. code-block:: php

    <?php

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->andWhere(
            $query->expr()->eq($query->getRootAliases()[0] . '.my_field', ':my_param')
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
    // src/AppBundle/Admin/PostAdmin.php

    use Sonata\AdminBundle\Admin\AbstractAdmin;

    class PostAdmin extends AbstractAdmin
    {
        // ...

        protected $datagridValues = array(

            // display the first page (default = 1)
            '_page' => 1,

            // reverse order (default = 'ASC')
            '_sort_order' => 'DESC',

            // name of the ordered field (default = the model's id field, if any)
            '_sort_by' => 'updatedAt',
        );

        // ...
    }

.. note::

    The ``_sort_by`` key can be of the form ``mySubModel.mySubSubModel.myField``.

.. note::

    **TODO**: how to sort by multiple fields (this might be a separate recipe?)

Filters
-------

You can add filters to let user control which data will be displayed.

.. code-block:: php

    <?php
    // src/AppBundle/Admin/PostAdmin.php

    use Sonata\AdminBundle\Datagrid\DatagridMapper;

    class ClientAdmin extends AbstractAdmin
    {

        protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        {
            $datagridMapper
                ->add('phone')
                ->add('email')
            ;
        }

        // ...
    }

All filters are hidden by default for space-saving. User has to check which filter he wants to use.

To make the filter always visible (even when it is inactive), set the parameter
``show_filter`` to ``true``.

.. code-block:: php

    <?php

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('phone')
            ->add('email', null, array(
                'show_filter' => true
            ))

            // ...
        ;
    }

By default the template generates an ``operator`` for a filter which defaults to ``sonata_type_equal``.
Though this ``operator_type`` is automatically detected it can be changed or even be hidden:

.. code-block:: php

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('foo', null, array(
                'operator_type' => 'sonata_type_boolean'
            ))
            ->add('bar', null, array(
                'operator_type' => 'hidden'
            ))

            // ...
        ;
    }

If you don't need the advanced filters, or all your ``operator_type`` are hidden, you can disable them by setting
``advanced_filter`` to ``false``. You need to disable all advanced filters to make the button disappear.

.. code-block:: php

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('bar', null, array(
                'operator_type' => 'hidden',
                'advanced_filter' => false
            ))

            // ...
        ;
    }

Default filters
^^^^^^^^^^^^^^^

Default filters can be added to the datagrid values by using the ``configureDefaultFilterValues`` method.
A filter has a ``value`` and an optional ``type``. If no ``type`` is given the default type ``is equal`` is used.

.. code-block:: php

    public function configureDefaultFilterValues(array &$filterValues)
    {
        $filterValues['foo'] = array(
            'type'  => ChoiceFilter::TYPE_CONTAINS,
            'value' => 'bar',
        );
    }

Available types are represented through classes which can be found here:
https://github.com/sonata-project/SonataCoreBundle/tree/master/Form/Type

Types like ``equal`` and ``boolean`` use constants to assign a choice of ``type`` to an ``integer`` for its ``value``:

.. code-block:: php

    <?php
    // SonataCoreBundle/Form/Type/EqualType.php

    namespace Sonata\CoreBundle\Form\Type;

    class EqualType extends AbstractType
    {
        const TYPE_IS_EQUAL = 1;
        const TYPE_IS_NOT_EQUAL = 2;
    }

The integers are then passed in the URL of the list action e.g.:
**/admin/user/user/list?filter[enabled][type]=1&filter[enabled][value]=1**

This is an example using these constants for an ``boolean`` type:

.. code-block:: php

    use Sonata\UserBundle\Admin\Model\UserAdmin as SonataUserAdmin;
    use Sonata\CoreBundle\Form\Type\EqualType;
    use Sonata\CoreBundle\Form\Type\BooleanType;

    class UserAdmin extends SonataUserAdmin
    {
        protected $datagridValues = array(
            'enabled' => array(
                'type'  => EqualType::TYPE_IS_EQUAL, // => 1
                'value' => BooleanType::TYPE_YES     // => 1
            )
        );
    }

Please note that setting a ``false`` value on a the ``boolean`` type will not work since the type expects an integer of  ``2`` as ``value`` as defined in the class constants:

.. code-block:: php

    <?php
    // SonataCoreBundle/Form/Type/BooleanType.php

    namespace Sonata\CoreBundle\Form\Type;

    class BooleanType extends AbstractType
    {
        const TYPE_YES = 1;
        const TYPE_NO = 2;
    }

Default filters can also be added to the datagrid values by overriding the ``getFilterParameters`` method.

.. code-block:: php

    use Sonata\CoreBundle\Form\Type\EqualType;
    use Sonata\CoreBundle\Form\Type\BooleanType;

    class UserAdmin extends SonataUserAdmin
    {
        public function getFilterParameters()
        {
            $this->datagridValues = array_merge(array(
                    'enabled' => array (
                        'type'  => EqualType::TYPE_IS_EQUAL,
                        'value' => BooleanType::TYPE_YES
                    )
                ), $this->datagridValues);

            return parent::getFilterParameters();
        }
    }

This approach is useful when you need to create dynamic filters.

.. code-block:: php

    class PostAdmin extends SonataUserAdmin
    {
        public function getFilterParameters()
        {
            // Assuming security context injected
            if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
                $user = $this->securityContext->getToken()->getUser();

                $this->datagridValues = array_merge(array(
                        'author' => array (
                            'type'  => EqualType::TYPE_IS_EQUAL,
                            'value' => $user->getId()
                        )
                    ), $this->datagridValues);
            }

            return parent::getFilterParameters();
        }
    }

Please note that this is not a secure approach to hide posts from others. It's just an example for setting filters on demand.

Callback filter
^^^^^^^^^^^^^^^

If you have the **SonataDoctrineORMAdminBundle** installed you can use the ``doctrine_orm_callback`` filter type e.g. for creating a full text filter:

.. code-block:: php

    use Sonata\UserBundle\Admin\Model\UserAdmin as SonataUserAdmin;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;

    class UserAdmin extends SonataUserAdmin
    {
        protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        {
            $datagridMapper
                ->add('full_text', CallbackFilter::class, array(
                    'callback' => array($this, 'getFullTextFilter'),
                    'field_type' => 'text'
                ))

                // ...
            ;
        }

        public function getFullTextFilter($queryBuilder, $alias, $field, $value)
        {
            if (!$value['value']) {
                return;
            }

            // Use `andWhere` instead of `where` to prevent overriding existing `where` conditions
            $queryBuilder->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->like($alias.'.username', $queryBuilder->expr()->literal('%' . $value['value'] . '%')),
                $queryBuilder->expr()->like($alias.'.firstName', $queryBuilder->expr()->literal('%' . $value['value'] . '%')),
                $queryBuilder->expr()->like($alias.'.lastName', $queryBuilder->expr()->literal('%' . $value['value'] . '%'))
            ));

            return true;
        }
    }

You can also get the filter type which can be helpful to change the operator type of your condition(s):

.. code-block:: php

    use Sonata\CoreBundle\Form\Type\EqualType;

    class UserAdmin extends SonataUserAdmin
    {
        public function getFullTextFilter($queryBuilder, $alias, $field, $value)
        {
            if (!$value['value']) {
                return;
            }

            $operator = $value['type'] == EqualType::TYPE_IS_EQUAL ? '=' : '!=';

            $queryBuilder
                ->andWhere($alias.'.username '.$operator.' :username')
                ->setParameter('username', $value['value'])
            ;

            return true;
        }
    }

.. note::

    **TODO**:
    * basic filter configuration and options
    * targeting submodel fields using dot-separated notation
    * advanced filter options (global_search)

Visual configuration
--------------------

You have the possibility to configure your List View to customize the render without overriding to whole template.
You can :

- `header_style`: Customize the style of header (width, color, background, align...)
- `header_class`: Customize the class of the header
- `row_align`:    Customize the alignment of the rendered inner cells

.. code-block:: php

    <?php

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('id', null, array(
                'header_style' => 'width: 5%; text-align: center',
                'row_align' => 'center'
            ))
            ->add('name', 'text', array(
                'header_style' => 'width: 35%'
            )
            ->add('actions', null, array(
                'header_class' => 'customActions',
                'row_align' => 'right'
            )

            // ...
        ;
    }

.. _`issues on GitHub`: https://github.com/sonata-project/SonataAdminBundle/issues/1519

Mosaic view button
------------------

You have the possibility to show/hide mosaic view button.

.. code-block:: yaml

    sonata_admin:
        # for hide mosaic view button on all screen using `false`
        show_mosaic_button:   true

You can show/hide mosaic view button using admin service configuration. You need to add option ``show_mosaic_button``
in your admin services:

.. code-block:: yaml

    sonata_admin.admin.post:
        class: Sonata\AdminBundle\Admin\PostAdmin
        arguments: [~, Sonata\AdminBundle\Entity\Post, ~]
        tags:
            - { name: sonata.admin, manager_type: orm, group: admin, label: Post, show_mosaic_button: true }

    sonata_admin.admin.news:
        class: Sonata\AdminBundle\Admin\NewsAdmin
        arguments: [~, Sonata\AdminBundle\Entity\News, ~]
        tags:
            - { name: sonata.admin, manager_type: orm, group: admin, label: News, show_mosaic_button: false }
