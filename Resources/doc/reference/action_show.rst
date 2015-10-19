The Show action
===============

.. note::

    This document is a stub representing a new work in progress. If you're reading
    this you can help contribute, **no matter what your experience level with Sonata
    is**. Check out the `issues on GitHub`_ for more information about how to get involved.

This document will cover the Show action and related configuration options.

Basic configuration
-------------------

To do:

- a note about Routes and how disabling them disables the related action
- a note about lifecycle events triggered by delete?
- options available when adding general fields, inc custom templates
- targeting submodel fields using dot-separated notation
- (Note, if this is very similar to the form documentation it can be combined)

Group options
~~~~~~~~~~~~~

When adding a group to your show page, you may specify some options for the group itself.

- ``collapsed``: unused at the moment
- ``class``: the class for your group in the admin; by default, the value is set to ``col-md-12``.
- ``fields``: the fields in your group (you should NOT override this unless you know what you're doing).
- ``box_class``: the class for your group box in the admin; by default, the value is set to ``box box-primary``.
- ``description``: to complete
- ``translation_domain``: to complete

To specify options, do as follow:

.. code-block:: php

    <?php
    // src/AppBundle/Admin/PersonAdmin.php

    class PersonAdmin extends Admin
    {
        public function configureShowFields(ShowMapper $showMapper)
        {
            $showMapper
                ->tab('General') // the tab call is optional
                    ->with('Addresses', array(
                        'class'       => 'col-md-8',
                        'box_class'   => 'box box-solid box-danger',
                        'description' => 'Lorem ipsum',
                    ))
                    ->add('title')

                    // ...

                    ->end()
                ->end()
            ;
    }

Customising the query used to show the object from within your Admin class
--------------------------------------------------------------------------

Setting up a showAction is pretty much the same as a form, which we did in the initial setup.

It is actually a bit easier, because we are only concerned with displaying information.

Smile, the hard part is already done.

The following is a working example of a ShowAction

.. code-block:: php

    <?php
    // src/AppBundle/Admin/PostAdmin.php

    use Sonata\AdminBundle\Show\ShowMapper;

    class ClientAdmin extends Admin
    {
        protected function configureShowFields(ShowMapper $showMapper)
        {
            // here we set the fields of the ShowMapper variable,
            // $showMapper (but this can be called anything)
            $showMapper

                 // The default option is to just display the
                 // value as text (for boolean this will be 1 or 0)
                ->add('name')
                ->add('phone')
                ->add('email')

                 // The boolean option is actually very cool
                 // true   shows a check mark and the 'yes' label
                 // false  shows a check mark and the 'no' label
                ->add('dateCafe', 'boolean')
                ->add('datePub', 'boolean')
                ->add('dateClub', 'boolean')
            ;

        }
    }

Setting up a custom show template (very useful)
===============================================

The first thing you need to do is define it in app/config/config/yml:

.. configuration-block::

    .. code-block:: yaml

        sonata_admin:
            title:      Acme
            title_logo: img/logo_small.png
            templates:
                show:       AppBundle:Admin:Display_Client.html.twig

Once you have defined this, Sonata Admin looks for it in the following location:

``src/AppBundle/Resources/views/Admin/Display_Client.html.twig``

Now that you have told Sonata Admin where to find the template, it is time to put one in there.

The recommended way to start is to copy the default template, and paste it into its new home.

This ensures that you can update Sonata Admin and keep all of your hard work.

The original template can be found in the following location:

``vendor/sonata-project/admin-bundle/Resources/views/CRUD/base_show.html.twig``

Now that you have a copy of the default template, check to make sure it works.

That's it, now go code.

.. _`issues on GitHub`: https://github.com/sonata-project/SonataAdminBundle/issues/1519
