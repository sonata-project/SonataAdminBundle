The Export action
=================

.. note::

    This document is a stub representing a new work in progress. If you're reading
    this you can help contribute, **no matter what your experience level with Sonata
    is**. Check out the `issues on GitHub`_ for more information about how to get involved.

This document will cover the Export action and related configuration options.

Basic configuration
-------------------

Translation
~~~~~~~~~~~

All field names are translated by default.
An internal mechanism checks if a field matching the translator strategy label exists in the current translation file
and will use the field name as a fallback.

Customizing export
~~~~~~~~~~~~~~~~~~

To customize available export formats just overwrite AbstractAdmin::getExportFormats()

.. code-block:: php

    <?php
    // src/AppBundle/Admin/PersonAdmin.php

    class PersonAdmin extends AbstractAdmin
    {
        /**
         * {@inheritdoc}
         */
        public function getExportFormats()
        {
            return array(
                'json', 'xml', 'csv', 'xls',
            );
        }
    }

If you want to customize the list of fields to export, overwrite AbstractAdmin::getExportFields() like
this:

.. code-block:: php

    <?php
    // src/AppBundle/Admin/PersonAdmin.php

    class PersonAdmin extends AbstractAdmin
    {
        /**
         * @return array
         */
        public function getExportFields() {
            return array(
                'Id' => 'id',
                'First Name' => 'firstName',
                'Last Name' => 'lastName',
                'Contact' => 'contact.phone',
            );
        }
    }

.. note::

    Note that you can use `contact.phone` to access the `phone` property of `Contact` entity

To add more customization to your export you can overwrite AbstractAdmin::getDataSourceIterator().
Supposing you want to change date format in your export file. You can do it like this:

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
    * any global (yml) options that affect the export actions
    * how to add new export formats
    * customising the query used to fetch the results

.. _`issues on Github`: https://github.com/sonata-project/SonataAdminBundle/issues/1519
.. _`the exporter bundle documentation`: https://github.com/sonata-project/exporter/blob/1.x/docs/reference/symfony.rst