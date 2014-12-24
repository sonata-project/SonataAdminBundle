Form Types
==========

Admin related form types
------------------------

When defining fields in your Admin classes you can use any of the standard
`Symfony field types`_ and configure them as you would normally. In addition
there are some special Sonata field types which allow you to work with
relationships between one entity class and another.

.. _field-types-model:

sonata_type_model
^^^^^^^^^^^^^^^^^

Setting a field type of ``sonata_type_model`` will use an instance of
``ModelType`` to render that field. This Type allows you to choose an existing
entity from the linked model class. In effect it shows a list of options from
which you can choose a value (or values).

For example, we have an entity class called ``Page`` which has a field called
``image1`` which maps a relationship to another entity class called ``Image``.
All we need to do now is add a reference for this field in our ``PageAdmin`` class:

.. code-block:: php

    class PageAdmin extends Admin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $imageFieldOptions = array(); // see available options below
            $formMapper
                ->add('image1', 'sonata_type_model', $imageFieldOptions)
            ;
        }
    }

Since the ``image1`` field refers to a related entity we do not need to specify
any options. Sonata will calculate that the linked class is of type ``Image`` and,
by default, retrieve a list of all existing Images to display as choices in the
selector.

Note that the third parameter to ``FormMapper::add()`` is optional so
there is no need to pass in an empty array, it is shown here just to demonstrate
where the options go when you want to use them.

The available options are:

property
  defaults to null. You can set this to a `Symfony PropertyPath`_ compatible
  string to designate which field to use for the choice values.

query
  defaults to null. You can set this to a QueryBuilder instance in order to
  define a custom query for retrieving the available options.

template
  defaults to 'choice' (not currently used?)

multiple
  defaults to false - see the `Symfony choice Field Type docs`_ for more info

expanded
  defaults to false - see the `Symfony choice Field Type docs`_ for more info

choices
  defaults to null - see the `Symfony choice Field Type docs`_ for more info

preferred_choices
  defaults to array() - see the `Symfony choice Field Type docs`_ for more info

choice_list
  defaults to a ``ModelChoiceList`` built from the other options

model_manager
  defaults to null, but is actually calculated from the linked Admin class.
  You usually should not need to set this manually.

class
  The entity class managed by this field. Defaults to null, but is actually
  calculated from the linked Admin class. You usually should not need to set
  this manually.

btn_add, btn_list, btn_delete and btn_catalogue:
  The labels on the ``add``, ``list`` and ``delete`` buttons can be customized
  with these parameters. Setting any of them to ``false`` will hide the
  corresponding button. You can also specify a custom translation catalogue
  for these labels, which defaults to ``SonataAdminBundle``.

sonata_type_model_hidden
^^^^^^^^^^^^^^^^^^^^^^^^
Setting a field type of ``sonata_type_model_hidden`` will use an instance of
``ModelHiddenType`` to render hidden field. The value of hidden field is
identifier of related entity.

.. code-block:: php

    class PageAdmin extends Admin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            // generate hidden form field with id of related Category entity
            $formMapper
                ->add('categoryId', 'sonata_type_model_hidden')
            ;
        }
    }

The available options are:

model_manager
  defaults to null, but is actually calculated from the linked Admin class.
  You usually should not need to set this manually.

class
  The entity class managed by this field. Defaults to null, but is actually
  calculated from the linked Admin class. You usually should not need to set
  this manually.

sonata_type_model_autocomplete
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Setting a field type of ``sonata_type_model_autocomplete`` will use an instance of
``ModelAutocompleteType`` to render that field. This Type allows you to choose an existing
entity from the linked model class. In effect it shows a list of options from
which you can choose a value. The list of options is loaded dynamically
with ajax after typing 3 chars (autocomplete). It is best for entities with many
items.

This field type works by default if the related entity has an admin instance and
in the related entity datagrid is a string filter on the ``property`` field.

For example, we have an entity class called ``Article`` (in the ``ArticleAdmin``)
which has a field called ``category`` which maps a relationship to another entity
class called ``Category``. All we need to do now is add a reference for this field
in our ``ArticleAdmin`` class and make sure, that in the CategoryAdmin exists
datagrid filter for the property ``title``.

.. code-block:: php

    class ArticleAdmin extends Admin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            // the dropdown autocomplete list will show only Category entities that contains specified text in "title" attribute
            $formMapper
                ->add('category', 'sonata_type_model_autocomplete', array('property'=>'title'))
            ;
        }
    }

    class CategoryAdmin extends Admin
    {
        protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        {
            // this text filter will be used to retrieve autocomplete fields
            $datagridMapper
                ->add('title')
            ;
        }
    }

The available options are:

property
  defaults to null. You have to set this to designate which field (or a list of fields) to use for the choice values.
  This value can be string or array of strings.

class
  The entity class managed by this field. Defaults to null, but is actually
  calculated from the linked Admin class. You usually should not need to set
  this manually.

model_manager
  defaults to null, but is actually calculated from the linked Admin class.
  You usually should not need to set this manually.

callback
  defaults to null. Callable function that can be used to modify the query which is used to retrieve autocomplete items.
  The callback should receive three parameters - the Admin instance, the property (or properties) defined as searchable and the
  search value entered by the user.

  From the ``$admin`` parameter it is possible to get the ``Datagrid`` and the ``Request``:

.. code-block:: php

    $formMapper
        ->add('category', 'sonata_type_model_autocomplete', array(
            'property'=>'title',
            'callback' => function ($admin, $property, $value) {
                $datagrid = $admin->getDatagrid();
                $queryBuilder = $datagrid->getQuery();
                $queryBuilder
                    ->andWhere($queryBuilder->getRootAlias() . '.foo=:barValue')
                    ->setParameter('barValue', $admin->getRequest()->get('bar'))
                ;
                $datagrid->setValue($property, null, $value);
            },
        )
    );

to_string_callback
  defaults to null. Callable function that can be used to change the default toString behaviour of entity.

.. code-block:: php

    $formMapper
        ->add('category', 'sonata_type_model_autocomplete', array(
            'property'=>'title',
            'to_string_callback' => function($enitity, $property) {
                return $enitity->getTitle();
            },
        )
    );

multiple
  defaults to false. Set to true, if you`re field is in many-to-many relation.

placeholder
  defaults to "". Placeholder is shown when no item is selected.

minimum_input_length
  defaults to 3. Minimum number of chars that should be typed to load ajax data.

items_per_page
  defaults to 10. Number of items per one ajax request.

url
  defaults to "". Target external remote url for ajax requests.
  You usually should not need to set this manually.

route
  The route ``name`` with ``parameters`` that is used as target url for ajax
  requests.

dropdown_css_class
  defaults to "sonata-autocomplete-dropdown". CSS class of dropdown list.

req_param_name_search
  defaults to "q". Ajax request parameter name which contains the searched text.

req_param_name_page_number
  defaults to "_page". Ajax request parameter name which contains the page number.

req_param_name_items_per_page
  defaults to "_per_page".  Ajax request parameter name which contains the limit of
  items per page.

sonata_type_admin
^^^^^^^^^^^^^^^^^

Setting a field type of ``sonata_type_admin`` will embed another Admin class
and use the embedded Admin's configuration when editing this field.
``sonata_type_admin`` fields should only be used when editing a field which
represents a relationship between two model classes.

This Type allows you to embed a complete form for the related element, which
you can configure to allow the creation, editing and (optionally) deletion of
related objects.

For example, lets use a similar example to the one for ``sonata_type_model`` above.
This time, when editing a ``Page`` using ``PageAdmin`` we want to enable the inline
creation (and editing) of new Images instead of just selecting an existing Image
from a list.

First we need to create an ``ImageAdmin`` class and register it as an Admin class
for managing ``Image`` objects. In our admin.yml we have an entry for ``ImageAdmin``
that looks like this:

.. configuration-block::

    .. code-block:: yaml

        # Acme/DemoBundle/Resources/config/admin.yml

        sonata.admin.image:
            class: Acme\DemoBundle\Admin\ImageAdmin
            tags:
                - { name: sonata.admin, manager_type: orm, label: "Image" }
            arguments:
                - ~
                - Acme\DemoBundle\Entity\Image
                - 'SonataAdminBundle:CRUD'
            calls:
                - [ setTranslationDomain, [Acme\DemoBundle]]


To embed ``ImageAdmin`` within ``PageAdmin`` we just need to change the reference
for the ``image1`` field to ``sonata_type_admin`` in our ``PageAdmin`` class:

.. code-block:: php

    class PageAdmin extends Admin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('image1', 'sonata_type_admin')
            ;
        }
    }

We do not need to define any options since Sonata calculates that the linked class
is of type ``Image`` and the service definition (in admin.yml) defines that ``Image``
objects are managed by the ``ImageAdmin`` class.

The available options (which can be passed as a third parameter to ``FormMapper::add()``) are:

delete
  defaults to true and indicates that a 'delete' checkbox should be shown allowing
  the user to delete the linked object.

btn_add, btn_list, btn_delete and btn_catalogue:
  The labels on the ``add``, ``list`` and ``delete`` buttons can be customized
  with these parameters. Setting any of them to ``false`` will hide the
  corresponding button. You can also specify a custom translation catalogue
  for these labels, which defaults to ``SonataAdminBundle``.


sonata_type_collection
^^^^^^^^^^^^^^^^^^^^^^

The ``Collection Type`` is meant to handle creation and editing of model
collections. Rows can be added and deleted, and your model abstraction layer may
allow you to edit fields inline. You can use ``type_options`` to pass values
to the underlying forms.

.. code-block:: php

    class AcmeProductAdmin extends Admin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('sales', 'sonata_type_collection', array(
                    'type_options' => array(
                        // Prevents the "Delete" option from being displayed
                        'delete' => false,
                        'delete_options' => array(
                            // You may otherwise choose to put the field but hide it
                            'type'         => 'hidden',
                            // In that case, you need to fill in the options as well
                            'type_options' => array(
                                'mapped'   => false,
                                'required' => false,
                            )
                        )
                    )
                ), array(
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable' => 'position',
                ))
            ;
        }
    }

The available options (which can be passed as a third parameter to ``FormMapper::add()``) are:

btn_add and btn_catalogue:
  The label on the ``add`` button can be customized
  with this parameters. Setting it to ``false`` will hide the
  corresponding button. You can also specify a custom translation catalogue
  for this label, which defaults to ``SonataAdminBundle``.

**TIP**: A jQuery event is fired after a row has been added (``sonata-admin-append-form-element``).
You can listen to this event to trigger custom javascript (eg: add a calendar widget to a newly added date field)

**TIP**: Setting the 'required' option to true does not cause a requirement of 'at least one' child entity.
Setting the 'required' option to false causes all nested form fields to become not required as well.

sonata_type_native_collection (previously collection)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This bundle handle the native symfony ``collection`` form type by adding:

* an ``add`` button if you set the ``allow_add`` option to ``true``.
* a ``delete`` button if you set the ``allow_delete`` option to ``true``.

.. TIP::

    A jQuery event is fired after a row has been added (``sonata-admin-append-form-element``).
    You can listen to this event to trigger custom javascript (eg: add a calendar widget to a newly added date field)

.. TIP::

    A jQuery event is fired after a row has been added (``sonata-collection-item-added``)
    or deleted (``sonata-collection-item-deleted``). You can listen to these events to trigger custom javascript.

FieldDescription options
^^^^^^^^^^^^^^^^^^^^^^^^

The fourth parameter to FormMapper::add() allows you to pass in ``FieldDescription``
options as an array. The most useful of these is ``admin_code``, which allows you to
specify which Admin to use for managing this relationship. It is most useful for inline
editing in conjunction with the ``sonata_type_admin`` form type.

The value used should be the admin *service* name, not the class name. If you do
not specify an ``admin_code`` in this way, the default admin class for the field's
model type will  be used.

For example, to specify the use of the Admin class which is registered as
``sonata.admin.imageSpecial`` for managing the ``image1`` field from our ``PageAdmin``
example above:

.. code-block:: php

    class PageAdmin extends Admin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add(
                  'image1',
                  'sonata_type_admin',
                  array(),
                  array('admin_code' => 'sonata.admin.imageSpecial')
                )
            ;
        }
    }

Other specific field configuration options are detailed in the related
abstraction layer documentation.

Types options
-------------

General
^^^^^^^

- ``label``: You can set the ``label`` option to ``false`` if you don't want to show it.

.. code-block:: php

        <?php
        $form->add('status', null, array('label' => false);

.. _`Symfony field types`: http://symfony.com/doc/current/book/forms.html#built-in-field-types
.. _`Symfony choice Field Type docs`: http://symfony.com/doc/current/reference/forms/types/choice.html
.. _`Symfony PropertyPath`: http://api.symfony.com/2.0/Symfony/Component/Form/Util/PropertyPath.html
