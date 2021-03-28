Inline Validation
=================

The inline validation code is now part of the Sonata Form Extensions.
You can refer to the related documentation for more information.

Validation group
----------------

To use a custom validation group, you can override the ``configureFormOptions()`` method::

    protected function configureFormOptions(array &$formOptions)
    {
        $formOptions['validation_groups'] = ['ExistingGroup', 'Default'];
    }
