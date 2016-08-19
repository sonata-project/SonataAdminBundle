The Export action
=================

This document will cover the Export action and related configuration options.

Basic configuration
-------------------

If you have registered the ``SonataExporterBundle`` bundle in the kernel of your application,
you can benefit from a lot of flexibility:

* You can configure default exporters globally.
* You can add custom exporters, also globally.
* You can configure every default writer.

See `the exporter bundle documentation`_ for more information.

Translation
~~~~~~~~~~~

All field names are translated by default.
An internal mechanism checks if a field matching the translator strategy label exists in the current translation file
and will use the field name as a fallback.

Picking which fields to export
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, all fields are exported. More accurately, it depends on the
persistence backend you are using, but for instance, the doctrine ORM backend
exports all fields (associations are not exported). If you want to change this
behavior for a specific admin, you can override the ``getExportFields()`` method:

.. code-block:: php

    <?php

    public function getExportFields()
    {
        return array('givenName', 'familyName', 'contact.phone');
    }

.. note::

    Note that you can use `contact.phone` to access the `phone` property of `Contact` entity

You can also tweak the list by creating an admin extension that implements the
``configureExportFields()`` method.

.. code-block:: php

    public function configureExportFields(AdminInterface $admin, array $fields)
    {
        unset($fields['updatedAt']);

        return $fields;
    }


Overriding the export formats for a specific admin
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Changing the export formats can be done by defining a ``getExportFormats()``
method in your admin class.

.. code-block:: php

    <?php

    public function getExportFormats()
    {
        return array('pdf', 'html');
    }

Customizing the query used to fetch the results
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
If you want to customize the query used to fetch the results for a specific admin,
you can override the ``getDataSourceIterator()`` method:

.. code-block:: php

    <?php
    // src/AppBundle/Admin/PersonAdmin.php

    class PersonAdmin extends AbstractAdmin
    {
        public function getDataSourceIterator()
        {
            $iterator = parent::getDataSourceIterator();
            $iterator->setDateTimeFormat('d/m/Y'); //change this to suit your needs
            return $iterator;
        }
    }

.. note::

    **TODO**:
    * customising the templates used to render the output
    * publish the exporter documentation on the project's website and update the link

.. _`the exporter bundle documentation`: https://github.com/sonata-project/exporter/blob/1.x/docs/reference/symfony.rst
