Creating and Editing objects
============================

.. note::

    This document is a stub representing a new work in progress. If you're reading
    this you can help contribute, **no matter what your experience level with Sonata
    is**. Check out the `issues on Github`_ for more information about how to get involved.

This document will cover the Create and Edit actions. It will cover configuration
of the fields and forms available in these views and any other relevant settings.


Basic configuration
-------------------

To do:

- global (yml) options that affect the create and edit actions
- a note about Routes and how disabling them disables the related action
- using configureFormFields() to set which fields to display
- options available when adding fields, inc custom templates
- link to the field_types document for more details about specific field types

FormGroup options
~~~~~~~~~~~~~~~~~

When adding a form group to your edit/create form, you may specify some options for the group itself.

- ``collapsed``: unused at the moment
- ``class``: the class for your form group in the admin; by default, the value is set to ``col-md-12``.
- ``fields``: the fields in your form group (you should NOT override this unless you know what you're doing).
- ``description``: to complete
- ``translation_domain``: to complete

To specify options, do as follow:

.. code-block:: php

    <?php

    MyAdmin extends Admin
    {
        # ...

        public function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->tab('General') // the tab call is optional
                    ->with('Addresses',
                        array(
                            'class'       => 'col-md-8',
                            'description' => 'Lorem ipsum',
                            // ...
                            ))
                        // ...
                    ->end()
                ->end()
            ;
    }

Embedding other Admins
----------------------

To do:

- how to embed one Admin in another (1:1, 1:M, M:M)
- how to access the right object(s) from the embedded Admin's code


Customizing just one of the actions
-----------------------------------

To do:

- how to create settings/fields that appear on just one of the create/edit views
  and any controller changes needed to manage them

.. _`issues on GitHub`: https://github.com/sonata-project/SonataAdminBundle/issues/1519
