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
            ->add('isVariation', FieldDescriptionInterface::TYPE_BOOLEAN)

            // if null, the type will be guessed
            ->add('enabled', null, [
                'editable' => true
            ])

            // editable association field
            ->add('status', FieldDescriptionInterface::TYPE_CHOICE, [
                'editable' => true,
                'class' => 'Vendor\ExampleBundle\Entity\ExampleStatus',
                'choices' => [
                    1 => 'Active',
                    2 => 'Inactive',
                    3 => 'Draft',
                ],
            ])

            // editable multiple field
            ->add('winner', FieldDescriptionInterface::TYPE_CHOICE, [
                'editable' => true,
                'multiple' => true,
                'choices' => [
                    'jury' => 'Jury',
                    'voting' => 'Voting',
                    'encouraging' => 'Encouraging',
                ],
            ])

            // we can add options to the field depending on the type
            ->add('price', FieldDescriptionInterface::TYPE_CURRENCY, [
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

            // you may also use a custom accessor
            ->add('description1', null, [
                'accessor' => 'getDescription'
            ])
            ->add('description2', null, [
                'accessor' => function ($subject) {
                    return $this->customService->formatDescription($subject);
                }
            ])

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

+---------------------------------------+---------------------+-----------------------------------------------------------------------+
| Type                                  | Options             | Description                                                           |
+=======================================+=====================+=======================================================================+
| ``ListMapper::TYPE_ACTIONS``          | actions             | List of available actions                                             |
+                                       +                     +                                                                       +
|                                       |   edit              | Name of the action (``show``, ``edit``, ``history``, ``delete``, etc) |
+                                       +                     +                                                                       +
|                                       |     link_parameters | Route parameters                                                      |
+---------------------------------------+---------------------+-----------------------------------------------------------------------+
| ``ListMapper::TYPE_BATCH``            |                     | Renders a checkbox                                                    |
+---------------------------------------+---------------------+-----------------------------------------------------------------------+
| ``ListMapper::TYPE_SELECT``           |                     | Renders a select box                                                  |
+---------------------------------------+---------------------+-----------------------------------------------------------------------+
| ``FieldDescriptionInterface::TYPE_*`` |                     | See :doc:`Field Types <field_types>`                                  |
+---------------------------------------+---------------------+-----------------------------------------------------------------------+

Symfony Data Transformers
^^^^^^^^^^^^^^^^^^^^^^^^^

If the model field has a limited list of values (enumeration), it is convenient to use a value object to control
the available values. For example, consider the value object of moderation status with the following values:
``awaiting``, ``approved``, ``rejected``::

    final class ModerationStatus
    {
        public const AWAITING = 'awaiting';
        public const APPROVED = 'approved';
        public const REJECTED = 'rejected';

        private static $instances = [];

        private string $value;

        private function __construct(string $value)
        {
            if (!array_key_exists($value, self::choices())) {
                throw new \DomainException(sprintf('The value "%s" is not a valid moderation status.', $value));
            }

            $this->value = $value;
        }

        public static function byValue(string $value): ModerationStatus
        {
            // limitation of count object instances
            if (!isset(self::$instances[$value])) {
                self::$instances[$value] = new static($value);
            }

            return self::$instances[$value];
        }

        public function getValue(): string
        {
            return $this->value;
        }

        public static function choices(): array
        {
            return [
                self::AWAITING => 'moderation_status.awaiting',
                self::APPROVED => 'moderation_status.approved',
                self::REJECTED => 'moderation_status.rejected',
            ];
        }

        public function __toString(): string
        {
            return self::choices()[$this->value];
        }
    }

To use this Value Object in the _`Symfony Form`: https://symfony.com/doc/current/forms.html component, we need a
_`Data Transformer`: https://symfony.com/doc/current/form/data_transformers.html ::

    use Symfony\Component\Form\DataTransformerInterface;
    use Symfony\Component\Form\Exception\TransformationFailedException;

    final class ModerationStatusDataTransformer implements DataTransformerInterface
    {
        public function transform($value): ?string
        {
            $status = $this->reverseTransform($value);

            return $status instanceof ModerationStatus ? $status->value() : null;
        }

        public function reverseTransform($value): ?ModerationStatus
        {
            if (null === $value || '' === $value) {
                return null;
            }

            if ($value instanceof ModerationStatus) {
                return $value;
            }

            try {
                return ModerationStatus::byValue($value);
            } catch (\Throwable $e) {
                throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

For quick moderation of objects, it is convenient to do this on the page for viewing all objects. But if we just
indicate the field as editable, then when editing we get in the object a string with the value itself (``awaiting``,
``approved``, ``rejected``), and not the Value Object (``ModerationStatus``). To solve this problem, you must specify
the Data Transformer in the ``data_transformer`` field so that it correctly converts the input data into the data
expected by your object::

    // ...

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('moderation_status', 'choice', [
                'editable' => true,
                'choices' => ModerationStatus::choices(),
                'data_transformer' => new ModerationStatusDataTransformer(),
            ])
        ;
    }


Customizing the query used to generate the list
-----------------------------------------------

.. versionadded:: 3.63

    The ``configureQuery`` method was introduced in 3.63.

You can customize the list query thanks to the ``configureQuery`` method::

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query = parent::configureQuery($query);

        $rootAlias = current($query->getRootAliases());

        $query->andWhere(
            $query->expr()->eq($rootAlias . '.my_field', ':my_param')
        );
        $query->setParameter('my_param', 'my_value');

        return $query;
    }

Customizing the sort order
--------------------------

Configure the default ordering in the list view
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Configuring the default ordering column can be achieved by overriding the
``configureDefaultSortValues()`` method. All three keys ``_page``, ``_sort_order`` and
``_sort_by`` can be omitted::

    // src/Admin/PostAdmin.php

    use Sonata\AdminBundle\Admin\AbstractAdmin;

    final class PostAdmin extends AbstractAdmin
    {
        // ...

        protected function configureDefaultSortValues(array &$sortValues): void
        {
            // display the first page (default = 1)
            $sortValues['_page'] = 1;

            // reverse order (default = 'ASC')
            $sortValues['_sort_order'] = 'DESC';

            // name of the ordered field (default = the model's id field, if any)
            $sortValues['_sort_by'] = 'updatedAt';
        }

        // ...
    }

.. note::

    The ``_sort_by`` key can be of the form ``mySubModel.mySubSubModel.myField``.

.. note::

    For UI reason, it's not possible to sort by multiple fields. However, this behavior can be simulate by
    adding some default orders in the ``configureQuery()`` method. The following example is using
    ``SonataAdminBundle`` with ``SonataDoctrineORMAdminBundle``::

        // src/Admin/PostAdmin.php

        use Sonata\AdminBundle\Admin\AbstractAdmin;

        final class PostAdmin extends AbstractAdmin
        {
            // ...

            protected function configureDefaultSortValues(array &$sortValues): void
            {
                // display the first page (default = 1)
                $sortValues['_page'] = 1;

                // reverse order (default = 'ASC')
                $sortValues['_sort_order'] = 'DESC';

                // name of the ordered field (default = the model's id field, if any)
                $sortValues['_sort_by'] = 'updatedAt';
            }

            protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
            {
                $rootAlias = current($query->getRootAliases());

                $query->addOrderBy($rootAlias.'.author', 'ASC');
                $query->addOrderBy($rootAlias.'.createdAt', 'ASC');

                return $query;
            }

            // ...
        }

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
        protected function configureDefaultFilterValues(array &$filterValues)
        {
            $filterValues['enabled'] = [
                'type'  => EqualType::TYPE_IS_EQUAL, // => 1
                'value' => BooleanType::TYPE_YES     // => 1
            ];
        }
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

This approach allow to create dynamic filters::

    class PostAdmin extends Sonata\UserBundle\Admin\Model\UserAdmin
    {
        protected function configureDefaultFilterValues(array &$filterValues)
        {
            // Assuming security context injected
            if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
                $user = $this->securityContext->getToken()->getUser();

                $filterValues['author'] = [
                    'type'  => EqualType::TYPE_IS_EQUAL,
                    'value' => $user->getId()
                ];
            }
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

        public function getFullTextFilter($query, $alias, $field, $value)
        {
            if (!$value['value']) {
                return false;
            }

            // Use `andWhere` instead of `where` to prevent overriding existing `where` conditions
            $query->andWhere($query->expr()->orX(
                $query->expr()->like($alias.'.username', $query->expr()->literal('%' . $value['value'] . '%')),
                $query->expr()->like($alias.'.firstName', $query->expr()->literal('%' . $value['value'] . '%')),
                $query->expr()->like($alias.'.lastName', $query->expr()->literal('%' . $value['value'] . '%'))
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
        public function getFullTextFilter($query, $alias, $field, $value)
        {
            if (!$value['value']) {
                return;
            }

            $operator = $value['type'] == EqualType::TYPE_IS_EQUAL ? '=' : '!=';

            $query
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
            ->add('name', FieldDescriptionInterface::TYPE_STRING, [
                'header_style' => 'width: 35%'
            ])
            ->add('description', FieldDescriptionInterface::TYPE_STRING, [
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

Displaying a non-model field
----------------------------

.. versionadded:: 3.73

  Support for displaying fields not part of the model class was introduced in version 3.73.

The list view can also display fields that are not part of the model class.

In some situations you can add a new getter to your model class to calculate
a field based on the other fields of your model::

    // src/Entity/User.php

    public function getFullName(): string
    {
        return $this->getGivenName().' '.$this->getFamilyName();
    }

    // src/Admin/UserAdmin.php

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('fullName');
    }

In situations where the data are not available in the model or it is more performant
to have the database calculate the value you can override the ``configureQuery()`` Admin
class method to add fields to the result set.
In ``configureListFields()`` these fields can be added using the alias given
in the query.

In the following example the number of comments for a post is added to the
query and displayed::

    // src/Admin/PostAdmin.php

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query = parent::configureQuery($query);

        $query
            ->leftJoin('n.Comments', 'c')
            ->addSelect('COUNT(c.id) numberofcomments')
            ->addGroupBy('n');

        return $query;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('numberofcomments');
    }

.. _`SonataDoctrineORMAdminBundle Documentation`: https://sonata-project.org/bundles/doctrine-orm-admin/master/doc/reference/list_field_definition.html
.. _`here`: https://github.com/sonata-project/form-extensions/tree/1.x/src/Type
