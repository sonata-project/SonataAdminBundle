Form Types
==========

Admin related form types
------------------------

The bundle come with different form types to handle model values:

sonata_type_model
^^^^^^^^^^^^^^^^^

The ``Model Type`` allows you to choose an existing model or create new ones. 
This type doesn't allow to directly edit the selected model.

sonata_type_admin
^^^^^^^^^^^^^^^^^

The ``Admin Type`` will delegate the form construction for this model to its 
related admin class. This type is useful to cascade edition or creation of 
linked models.

sonata_type_collection
^^^^^^^^^^^^^^^^^^^^^^

The ``Collection Type`` is meant to handle creation and edition of model 
collections. Rows can be added and deleted, and your model abstraction layer may
allow you to edit fields inline.

**TIP**: A jQuery event is fired after a row has been added(*sonata-collection-item-added*) or deleted(*sonata-collection-item-deleted*). You can bind to them to trigger some custom javascript imported into your templates(eg: add a calendar widget to a just added date field)

Type configuration
^^^^^^^^^^^^^^^^^^

todo


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
