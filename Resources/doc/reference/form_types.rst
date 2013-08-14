Form Types
==========

Admin related form types
------------------------

When defining fields in your Admin classes you can use any of the standard
`Symfony field types`_ and configure them as you would normally. In addition
there are some special Sonata field types which allow you to work with 
relationships between one entity class and another.

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
            $barFieldOptions = array(); // see available options below
            $formMapper
                ->add('image1', 'sonata_type_model', $barFieldOptions)
            ;
        }
    }

Since the ``image1`` field refers to a related entity we do not need to specify 
any options. Sonata will calculate that the linked class is of type ``Image`` and, 
by default, retrieve a list of all existing Images to display as choices in the 
selector.

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


sonata_type_admin
^^^^^^^^^^^^^^^^^

The ``Admin Type`` will delegate the form construction for this model to its 
related admin class. This type is useful to cascade edition or creation of 
linked models.

sonata_type_collection
^^^^^^^^^^^^^^^^^^^^^^

The ``Collection Type`` is meant to handle creation and edition of model 
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
                    //Prevents the "Delete" option from being displayed
                    'type_options' => array('delete' => false)
                ), array(
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable' => 'position',
                ))
            ;
        }
    }

**TIP**: A jQuery event is fired after a row has been added (``sonata-collection-item-added``) 
or deleted (``sonata-collection-item-deleted``). You can bind to these events to trigger custom 
javascript imported into your templates (eg: add a calendar widget to a newly added date field)

Field configuration
^^^^^^^^^^^^^^^^^^^

- ``admin_code``: Force for any field involving a model the admin class used to 
  handle it (useful for inline editing with ``sonata_type_admin``). The 
  expected value here is the admin service name, not the class name. If not 
  defined, the default admin class for the model type will be used (even if 
  you didn't define any admin for the model type).

Other specific field configuration options are detailed in the related 
abstraction layer documentation.

Other form types
----------------

The bundle comes with some handy form types which are available from outside the
scope of the ``SonataAdminBundle``:

sonata_type_immutable_array
^^^^^^^^^^^^^^^^^^^^^^^^^^^

The ``Immutable Array`` allows you to edit an array property by defining a type 
per key.

The type has a ``keys`` parameter which contains the definition for each key. 
A definition is an array with 3 options :

* key name
* type : a type name or a ``FormType`` instance
* related type parameters : please refer to the related form documentation.

Let's say a ``Page`` have options property with some fixed key-pair values, each
value has a different type : integer, url, or string for instance.

.. code-block:: php

    <?php
    class Page
    {
        protected $options = array(
            'ttl'       => 1,
            'redirect'  => ''
        );

        public function setOptions(array $options)
        {
            $this->options = $options;
        }

        public function getOptions()
        {
            return $this->options;
        }
    }

Now, the property can be edited by setting a type for each type

.. code-block:: php

        <?php
        $form->add('options', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('ttl',        'text', array('required' => false)),
                array('redirect',   'url',  array('required' => true)),
            )
        ));


sonata_type_boolean
^^^^^^^^^^^^^^^^^^^

The ``boolean`` type is a specialized ``ChoiceType`` where the choices list is 
locked to 'yes' and 'no'.


sonata_type_translatable_choice
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Deprecated: use ChoiceType with the translation_domain option instead.

The translatable type is a specialized ``ChoiceType`` where the choices values 
are translated with the Symfony Translator component.

The type has one extra parameter :

 * ``catalogue`` : the catalogue name to translate the value


.. code-block:: php

    <?php

    // The delivery list
    class Delivery
    {
        public static function getStatusList()
        {
            return array(
                self::STATUS_OPEN      => 'status_open',
                self::STATUS_PENDING   => 'status_pending',
                self::STATUS_VALIDATED => 'status_validated',
                self::STATUS_CANCELLED => 'status_cancelled',
                self::STATUS_ERROR     => 'status_error',
                self::STATUS_STOPPED   => 'status_stopped',
            );
        }
    }

    // form usage
    $form->add('deliveryStatus', 'sonata_type_translatable_choice', array(
        'choices' => Delivery::getStatusList(),
        'catalogue' => 'SonataOrderBundle'
    ))

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
