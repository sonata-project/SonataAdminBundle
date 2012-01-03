Form Types
==========

Admin related form types
------------------------

// todo


Other form types
----------------

The bundle comes with some handy form types which are available from outside the scope of the ``SonataAdminBundle``::

sonata_type_immutable_array
^^^^^^^^^^^^^^^^^^^^^^^^^^^

The ``Immutable Array`` allows you to edit an array property by defining a type per key.

The type has a ``keys`` parameter which contains the definition for each key. A definition is an array with 3 options :
* key name
* type : a type name or a ``FormType`` instance
* related type parameters : please refer to the related form documentation.

Let's say a ``Page`` have options property with some fixed key-pair values, each value has a different type : integer,
url, or string for instance.

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

The ``boolean`` type is a specialized ``ChoiceType`` where the choices list is locked to 'yes' and 'no'.


sonata_type_translatable_choice
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The translatable type is a specialized ``ChoiceType`` where the choices values are translated with the Symfony
Translator component.

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
