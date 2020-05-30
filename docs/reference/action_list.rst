The List View
=============

This document will cover the List view which you use to browse the objects in your
system. It will cover configuration of the list itself and the filters you can use
to control what's visible.

Basic configuration
-------------------

SonataAdmin Options that may affect the list view:

.. code-block:: yaml

    # config/packages/sonata_admin.yaml

    sonata_admin:
        templates:
            list:                       '@SonataAdmin/CRUD/list.html.twig'
            action:                     '@SonataAdmin/CRUD/action.html.twig'
            select:                     '@SonataAdmin/CRUD/list__select.html.twig'
            list_block:                 '@SonataAdmin/Block/block_admin_list.html.twig'
            short_object_description:   '@SonataAdmin/Helper/short-object-description.html.twig'
            batch:                      '@SonataAdmin/CRUD/list__batch.html.twig'
            inner_list_row:             '@SonataAdmin/CRUD/list_inner_row.html.twig'
            base_list_field:            '@SonataAdmin/CRUD/base_list_field.html.twig'
            pager_links:                '@SonataAdmin/Pager/links.html.twig'
            pager_results:              '@SonataAdmin/Pager/results.html.twig'

.. note::

    **TODO**:
    * a note about Routes and how disabling them disables the related action
    * adding custom columns

Customizing the fields displayed on the list page
-------------------------------------------------

You can customize the columns displayed on the list through the ``configureListFields`` method.
Here is an example::

    // ...

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            // addIdentifier allows to specify that this column
            // will provide a link to the entity
            // (edit or show route, depends on your access rights)
            ->addIdentifier('name')

            // you may specify the field type directly as the
            // second argument instead of in the options
            ->add('isVariation', TemplateRegistry::TYPE_BOOLEAN)

            // if null, the type will be guessed
            ->add('enabled', null, [
                'editable' => true
            ])

            // editable association field
            ->add('status', TemplateRegistry::TYPE_CHOICE, [
                'editable' => true,
                'class' => 'Vendor\ExampleBundle\Entity\ExampleStatus',
                'choices' => [
                    1 => 'Active',
                    2 => 'Inactive',
                    3 => 'Draft',
                ],
            ])

            // editable multiple field
            ->add('winner', TemplateRegistry::TYPE_CHOICE, [
                'editable' => true,
                'multiple' => true,
                'choices' => [
                    'jury' => 'Jury',
                    'voting' => 'Voting',
                    'encouraging' => 'Encouraging',
                ],
            ])

            // we can add options to the field depending on the type
            ->add('price', TemplateRegistry::TYPE_CURRENCY, [
                'currency' => $this->currencyDetector->getCurrency()->getLabel()
            ])

            // Here we specify which property is used to render the label of each entity in the list
            ->add('productCategories', null, [
                'associated_property' => 'name'
                // By default, sorting will be done on the associated property.
                // To sort on another property, add the following:
                'sort_field_mapping' => [
                    'fieldName' => 'weight',
                ],
            ])

            // you may also use dotted-notation to access
            // specific properties of a relation to the entity
            ->add('image.name')

            // You may also specify the actions you want to be displayed in the list
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [
                        // You may add custom link parameters used to generate the action url
                        'link_parameters' => [
                            'full' => true,
                        ]
                    ],
                    'delete' => [],
                ]
            ])

        ;
    }

Options
^^^^^^^

.. note::

    * ``(m)`` stands for mandatory
    * ``(o)`` stands for optional

- ``type`` (m): defines the field type - mandatory for the field description
  itself but will try to detect the type automatically if not specified
- ``template`` (o): the template used to render the field
- ``label`` (o): the name used for the column's title
- ``link_parameters`` (o): add link parameter to the related Admin class
  when the ``Admin::generateUrl`` is called
- ``code`` (o): the method name to retrieve the related value (for example,
  if you have an `array` type field, you would like to show info prettier
  than `[0] => 'Value'`; useful when a getter is not enough).
  Notice: works with string-like types (string, text, html)
- ``associated_property`` (o): property path to retrieve the "string"
  representation of the collection element, or a closure with the element
  as argument and return a string.
- ``sort_field_mapping`` (o): property of the collection element to sort on.
- ``identifier`` (o): if set to true a link appears on the value to edit the element

Available types and associated options
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. note::

    ``(m)`` means that option is mandatory

+-----------+---------------------+-----------------------------------------------------------------------+
| Type      | Options             | Description                                                           |
+===========+=====================+=======================================================================+
| actions   | actions             | List of available actions                                             |
+           +                     +                                                                       +
|           |   edit              | Name of the action (``show``, ``edit``, ``history``, ``delete``, etc) |
+           +                     +                                                                       +
|           |     link_parameters | Route parameters                                                      |
+-----------+---------------------+-----------------------------------------------------------------------+
| batch     |                     | Renders a checkbox                                                    |
+-----------+---------------------+-----------------------------------------------------------------------+
| select    |                     | Renders a select box                                                  |
+-----------+---------------------+-----------------------------------------------------------------------+
| array     |                     | Displays an array                                                     |
+-----------+---------------------+-----------------------------------------------------------------------+
| boolean   | ajax_hidden         | Yes/No; ajax_hidden allows to hide list field during an AJAX context. |
+           +---------------------+-----------------------------------------------------------------------+
|           | editable            | Yes/No; editable allows to edit directly from the list if authorized. |
+           +---------------------+-----------------------------------------------------------------------+
|           | inverse             | Yes/No; reverses the background color (green for false, red for true) |
+-----------+---------------------+-----------------------------------------------------------------------+
| choice    | choices             | Possible choices                                                      |
+           +---------------------+-----------------------------------------------------------------------+
|           | multiple            | Is it a multiple choice option? Defaults to false.                    |
+           +---------------------+-----------------------------------------------------------------------+
|           | delimiter           | Separator of values if multiple.                                      |
+           +---------------------+-----------------------------------------------------------------------+
|           | catalogue           | Translation catalogue.                                                |
+           +---------------------+-----------------------------------------------------------------------+
|           | class               | Class path for editable association field.                            |
+-----------+---------------------+-----------------------------------------------------------------------+
| currency  | currency (m)        | A currency string (EUR or USD for instance).                          |
+-----------+---------------------+-----------------------------------------------------------------------+
| date      | format              | A format understandable by Twig's ``date`` function.                  |
+           +---------------------+-----------------------------------------------------------------------+
|           | timezone            | Second argument for Twig's ``date`` function                          |
+-----------+---------------------+-----------------------------------------------------------------------+
| datetime  | format              | A format understandable by Twig's ``date`` function.                  |
+           +---------------------+-----------------------------------------------------------------------+
|           | timezone            | Second argument for Twig's ``date`` function                          |
+-----------+---------------------+-----------------------------------------------------------------------+
| email     | as_string           | Renders the email as string, without any link.                        |
+           +---------------------+-----------------------------------------------------------------------+
|           | subject             | Add subject parameter to email link.                                  |
+           +---------------------+-----------------------------------------------------------------------+
|           | body                | Add body parameter to email link.                                     |
+-----------+---------------------+-----------------------------------------------------------------------+
| percent   |                     | Renders value as a percentage.                                        |
+-----------+---------------------+-----------------------------------------------------------------------+
| string    |                     | Renders a string.                                                     |
+-----------+---------------------+-----------------------------------------------------------------------+
| text      |                     | See 'string'                                                          |
+-----------+---------------------+-----------------------------------------------------------------------+
| html      |                     | Renders string as html                                                |
+-----------+---------------------+-----------------------------------------------------------------------+
| time      |                     | Renders a datetime's time with format ``H:i:s``.                      |
+-----------+---------------------+-----------------------------------------------------------------------+
| trans     | catalogue           | Translates the value with catalogue ``catalogue`` if defined.         |
+-----------+---------------------+-----------------------------------------------------------------------+
| url       | url                 | Adds a link with url ``url`` to the displayed value                   |
+           +---------------------+-----------------------------------------------------------------------+
|           | route               | Give a route to generate the url                                      |
+           +                     +                                                                       +
|           |   name              | Route name                                                            |
+           +                     +                                                                       +
|           |   parameters        | Route parameters                                                      |
+           +---------------------+-----------------------------------------------------------------------+
|           | hide_protocol       | Hide http:// or https:// (default: false)                             |
+-----------+---------------------+-----------------------------------------------------------------------+

If you have the SonataDoctrineORMAdminBundle installed, you have access
to more field types, see `SonataDoctrineORMAdminBundle Documentation`_.

.. note::

    It is better to prefer non negative notions when possible for boolean
    values so use the ``inverse`` option if you really cannot find a good enough
    antonym for the name you have.

Customizing the query used to generate the list
-----------------------------------------------

.. versionadded:: 3.63

    The ``configureQuery`` method was introduced in 3.63.

You can customize the list query thanks to the ``configureQuery`` method::

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query = parent::configureQuery($query);
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

Configuring the default ordering column can be achieved by overriding the
``datagridValues`` array property. All three keys ``_page``, ``_sort_order`` and
``_sort_by`` can be omitted::

    // src/Admin/PostAdmin.php

    use Sonata\AdminBundle\Admin\AbstractAdmin;

    final class PostAdmin extends AbstractAdmin
    {
        // ...

        protected $datagridValues = [

            // display the first page (default = 1)
            '_page' => 1,

            // reverse order (default = 'ASC')
            '_sort_order' => 'DESC',

            // name of the ordered field (default = the model's id field, if any)
            '_sort_by' => 'updatedAt',
        ];

        // ...
    }

.. note::

    The ``_sort_by`` key can be of the form ``mySubModel.mySubSubModel.myField``.

.. note::

    **TODO**: how to sort by multiple fields (this might be a separate recipe?)

Filters
-------

You can add filters to let user control which data will be displayed::

    // src/Admin/PostAdmin.php

    use Sonata\AdminBundle\Datagrid\DatagridMapper;

    final class ClientAdmin extends AbstractAdmin
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

All filters are hidden by default for space-saving. User has to check which
filter he wants to use.

To make the filter always visible (even when it is inactive), set the parameter
``show_filter`` to ``true``::

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('phone')
            ->add('email', null, [
                'show_filter' => true
            ])

            // ...
        ;
    }

By default the template generates an ``operator`` for a filter which defaults to ``sonata_type_equal``.
Though this ``operator_type`` is automatically detected it can be changed or even be hidden::

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('foo', null, [
                'operator_type' => 'sonata_type_boolean'
            ])
            ->add('bar', null, [
                'operator_type' => 'hidden'
            ])

            // ...
        ;
    }

If you don't need the advanced filters, or all your ``operator_type``
are hidden, you can disable them by setting ``advanced_filter`` to ``false``.
You need to disable all advanced filters to make the button disappear::

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('bar', null, [
                'operator_type' => 'hidden',
                'advanced_filter' => false
            ])

            // ...
        ;
    }

Default filters
^^^^^^^^^^^^^^^

Default filters can be added to the datagrid values by using the ``configureDefaultFilterValues`` method.
A filter has a ``value`` and an optional ``type``. If no ``type`` is
given the default type ``is equal`` is used::

    protected function configureDefaultFilterValues(array &$filterValues)
    {
        $filterValues['foo'] = [
            'type'  => ChoiceType::TYPE_CONTAINS,
            'value' => 'bar',
        ];
    }

Available types are represented through classes which can be found `here`_.

Types like ``equal`` and ``boolean`` use constants to assign a choice of
``type`` to an ``integer`` for its ``value``::

    namespace Sonata\Form\Type;

    class EqualType extends AbstractType
    {
        const TYPE_IS_EQUAL = 1;
        const TYPE_IS_NOT_EQUAL = 2;
    }

The integers are then passed in the URL of the list action e.g.:
**/admin/user/user/list?filter[enabled][type]=1&filter[enabled][value]=1**

This is an example using these constants for an ``boolean`` type::

    use Sonata\Form\Type\EqualType;
    use Sonata\Form\Type\BooleanType;

    class UserAdmin extends Sonata\UserBundle\Admin\Model\UserAdmin
    {
        protected $datagridValues = [
            'enabled' => [
                'type'  => EqualType::TYPE_IS_EQUAL, // => 1
                'value' => BooleanType::TYPE_YES     // => 1
            ]
        ];
    }

Please note that setting a ``false`` value on a the ``boolean`` type
will not work since the type expects an integer of  ``2`` as ``value``
as defined in the class constants::

    namespace Sonata\Form\Type;

    class BooleanType extends AbstractType
    {
        const TYPE_YES = 1;
        const TYPE_NO = 2;
    }

Default filters can also be added to the datagrid values by overriding
the ``getFilterParameters`` method::

    use Sonata\Form\Type\EqualType;
    use Sonata\Form\Type\BooleanType;

    class UserAdmin extends Sonata\UserBundle\Admin\Model\UserAdmin
    {
        public function getFilterParameters()
        {
            $this->datagridValues = array_merge([
                'enabled' => [
                    'type'  => EqualType::TYPE_IS_EQUAL,
                    'value' => BooleanType::TYPE_YES
                ]
            ], $this->datagridValues);

            return parent::getFilterParameters();
        }
    }

This approach is useful when you need to create dynamic filters::

    class PostAdmin extends Sonata\UserBundle\Admin\Model\UserAdmin
    {
        public function getFilterParameters()
        {
            // Assuming security context injected
            if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
                $user = $this->securityContext->getToken()->getUser();

                $this->datagridValues = array_merge([
                    'author' => [
                        'type'  => EqualType::TYPE_IS_EQUAL,
                        'value' => $user->getId()
                    ]
                ], $this->datagridValues);
            }

            return parent::getFilterParameters();
        }
    }

.. note::

    this is not a secure approach to hide posts from others.
    It's only an example for setting filters on demand!

Callback filter
^^^^^^^^^^^^^^^

If you have the **SonataDoctrineORMAdminBundle** installed you can use the
``CallbackFilter`` filter type e.g. for creating a full text filter::

    use Sonata\AdminBundle\Datagrid\DatagridMapper;

    final class UserAdmin extends Sonata\UserBundle\Admin\Model\UserAdmin
    {
        protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        {
            $datagridMapper
                ->add('full_text', CallbackFilter::class, [
                    'callback' => [$this, 'getFullTextFilter'],
                    'field_type' => TextType::class,
                ]);
        }

        public function getFullTextFilter($queryBuilder, $alias, $field, $value)
        {
            if (!$value['value']) {
                return false;
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

The callback function should return a boolean indicating whether it is active.

You can also get the filter type which can be helpful to change the operator
type of your condition(s)::

    use Sonata\Form\Type\EqualType;

    final class UserAdmin extends Sonata\UserBundle\Admin\Model\UserAdmin
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

You have the possibility to configure your List View to customize the
render without overriding to whole template.

The following options are available:

- `header_style`: Customize the style of header (width, color, background, align...)
- `header_class`: Customize the class of the header
- `collapse`: Allow to collapse long text fields with a "read more" link
- `row_align`: Customize the alignment of the rendered inner cells
- `label_icon`: Add an icon before label

Example::

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->add('id', null, [
                'header_style' => 'width: 5%; text-align: center',
                'row_align' => 'center'
            ])
            ->add('name', TemplateRegistry::TYPE_STRING, [
                'header_style' => 'width: 35%'
            ])
            ->add('description', TemplateRegistry::TYPE_STRING, [
                'header_style' => 'width: 35%',
                'collapse' => true
            ])
            ->add('upvotes', null, [
                'label_icon' => 'fa fa-thumbs-o-up'
            ])
            ->add('actions', null, [
                'header_class' => 'customActions',
                'row_align' => 'right'
            ])
        ;
    }

If you want to customise the `collapse` option, you can also give an array
to override the default parameters::

            ->add('description', TextType::class, [
                'header_style' => 'width: 35%',
                'collapse' => [
                    // height in px
                    'height' => 40,

                    // content of the "read more" link
                    'more' => 'I want to see the full description',

                     // content of the "read less" link
                    'less' => 'This text is too long, reduce the size',
                ]
            ])

If you want to show only the `label_icon`::

            ->add('upvotes', null, [
                'label' => false,
                'label_icon' => 'fa fa-thumbs-o-up',
            ])

Mosaic view button
------------------

You have the possibility to show/hide mosaic view button.

.. code-block:: yaml

    # config/packages/sonata_admin.yaml

    sonata_admin:
        # for hide mosaic view button on all screen using `false`
        show_mosaic_button: true

You can show/hide mosaic view button using admin service configuration.
You need to add option ``show_mosaic_button`` in your admin services:

.. code-block:: yaml

    # config/services.yaml

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

Checkbox range selection
------------------------

.. tip::

    You can check / uncheck a range of checkboxes by clicking a first one,
    then a second one with shift + click.

.. _`SonataDoctrineORMAdminBundle Documentation`: https://sonata-project.org/bundles/doctrine-orm-admin/master/doc/reference/list_field_definition.html
.. _`here`: https://github.com/sonata-project/SonataCoreBundle/tree/3.x/src/Form/Type
