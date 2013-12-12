The Show action
===============

.. note::

    This document is a stub representing a new work in progress. If you're reading 
    this you can help contribute, **no matter what your experience level with Sonata 
    is**. Check out the ``issues on Github`` _ for more information about how to get involved.

This document will cover the Show action and related configuration options.


Basic configuration
-------------------

To do:

- a note about Routes and how disabling them disables the related action
- a note about lifecycle events triggered by delete?
- options available when adding general fields, inc custom templates
- targeting submodel fields using dot-separated notation
- (Note, if this is very similar to the form documentation it can be combined)



Customising the query used to show the object from within your Admin class
--------------------------------------------------------------------------

Setting up a showAction is pretty much the same as a form, which we did in the initial setup.

It is actually a but easier, because we are only concerned with displaying information.

Smile, the hard part is already done.

The following is a working example of a ShowAction

.. code-block:: php

    <?php
    // src/Acme/DemoBundle/Admin/PostAdmin.php

    class ClientAdmin extends Admin
    {
        protected function configureShowFields(ShowMapper $showMapper)
        {
            // Here we set the fields of the ShowMapper variable, $showMapper (but this can be called anything)
            $showMapper

                /*
                 * The default option is to just display the value as text (for boolean this will be 1 or 0)
                 */
                ->add('Name')
                ->add('Phone')
                ->add('Email')

                /*
                 * The boolean option is actually very cool
                 * - True  shows a check mark and says 'yes'
                 * - False shows an 'X' and says 'no'
                 */
                ->add('DateCafe','boolean')
                ->add('DatePub','boolean')
                ->add('DateClub','boolean')
            ;

        }
    }


Setting up a custom show template (very useful)
===============================================


The first thing you need to do is define it in app/config/config/yml:

.. code-block:: yml

    sonata_admin:
        title:      Acme Admin Area
        title_logo: img/logo_small.png
        templates:
            show:       AcmeDemoBundle:Admin:Display_Client.html.twig


Once you have defined this, Sonata Admin looks for it in the following location:

``src/Acme/DemoBundle/Resources/views/Admin/Display_Client.html.twig``

Now that you have told Sonata Admin where to find the template, it's time to put one in there.

The recommended way to start is to copy the default template, and paste it into it's new home.

This ensures that you can update Sonata Admin and keep all of your hard work.

The original template can be found in the following location:

``vendor/sonata-project/admin-bundle/Sonata/AdminBundle/Resources/views/CRUD/base_show.html.twig``

Now that you have a copy of the default template, check to make sure it works.

That's it, now go code.
