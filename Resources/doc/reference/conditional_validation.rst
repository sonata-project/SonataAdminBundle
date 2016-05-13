Inline Validation
=================

The inline validation code is now part of the SonataCoreBundle. You can refer to the related documentation for more information.

The above examples show how the integration has been done with the SonataAdminBundle. For completeness, it's worth remembering that
the ``Admin`` class itself contains an empty ``validate`` method. This is automatically called, so you can override it in your own admin class:

.. code-block:: php

    // add this to your existing use statements
    use Sonata\CoreBundle\Validator\ErrorElement;

    class MyAdmin extends AbstractAdmin
    {
        // add this method
        public function validate(ErrorElement $errorElement, $object)
        {
            $errorElement
                ->with('name')
                    ->assertLength(array('max' => 32))
                ->end()
            ;
        }

Troubleshooting
---------------

Make sure your validator method is being called. If in doubt, try throwing an exception:

.. code-block:: php

    public function validate(ErrorElement $errorElement, $object)
    {
        throw new \Exception(__METHOD__);
    }

There should not be any validation_groups defined for the form. If you have code like the example below in
your ``Admin`` class, remove the 'validation_groups' entry, the whole $formOptions property or set validation_groups
to an empty array:

.. code-block:: php

    protected $formOptions = array(
        'validation_groups' => array()
    );
