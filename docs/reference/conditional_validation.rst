Inline Validation
=================

The inline validation code is now part of the Sonata Form Extensions.
You can refer to the related documentation for more information.

The above examples show how the integration has been done with the SonataAdminBundle.
For completeness, it's worth remembering that the ``Admin`` class itself contains an
empty ``validate`` method. This is automatically called, so you can override
it in your own admin class::

    // add this to your existing use statements
    use Sonata\Form\Validator\ErrorElement;

    final class MyAdmin extends AbstractAdmin
    {
        // add this method
        public function validate(ErrorElement $errorElement, $object)
        {
            $errorElement
                ->with('name')
                    ->assertLength(['max' => 32])
                ->end()
            ;
        }

Troubleshooting
---------------

Make sure your validator method is being called. If in doubt, try throwing
an exception::

    public function validate(ErrorElement $errorElement, $object)
    {
        throw new \Exception(__METHOD__);
    }

There should not be any validation_groups defined for the form. If you have
code like the example below in your ``Admin`` class, remove the 'validation_groups'
entry, the whole $formOptions property or set validation_groups to an empty array::

    protected $formOptions = [
        'validation_groups' => []
    ];

If there are some validation groups already configured you want to keep, for
example if you use ``UserAdmin`` from SonataUserBundle, you can add the
``'Default'`` entry::

    protected $formOptions = [
        'validation_groups' => ['ExistingGroup', 'Default']
    ];
