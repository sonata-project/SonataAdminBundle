The Export action
=================

This document will cover the Export action and related configuration options.

Basic configuration
-------------------

If you have registered the ``SonataExporterBundle`` bundle, you can benefit
from a lot of flexibility:

* You can configure default exporters globally.
* You can add custom exporters, also globally.
* You can configure every default writer.

See `the exporter bundle documentation`_ for more information.

Routes
~~~~~~

You can disable exporting entities by removing the corresponding routes in your Admin.
For more detailed information about routes, see :doc:`routing`::

    // src/Admin/PersonAdmin.php

    final class PersonAdmin extends AbstractAdmin
    {
        protected function configureRoutes(RouteCollectionInterface $collection): void
        {
            // Removing the export route will disable exporting entities.
            $collection->remove('export');
        }
    }

Translation
~~~~~~~~~~~

All field names are translated by default.
An internal mechanism checks if a field matching the translator strategy
label exists in the current translation file and will use the field name
as a fallback.

Picking which fields to export
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

By default, all fields are exported. More accurately, it depends on the
persistence backend you are using, but for instance, the doctrine ORM backend
exports all fields (associations are not exported). If you want to change this
behavior for a specific admin, you can override the ``configureExportFields()`` method::

    protected function configureExportFields(): array
    {
        return ['givenName', 'familyName', 'contact.phone', 'getAddress'];
    }

.. note::

    Note that you can use ``contact.phone`` to access the ``phone`` property
    of `Contact` entity. Or use a getter if you have some virtual field.

You can also tweak the list by creating an admin extension that implements the
``configureExportFields()`` method::

    public function configureExportFields(AdminInterface $admin, array $fields): array
    {
        unset($fields['updatedAt']);

        return $fields;
    }

Overriding the export formats for a specific admin
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Changing the export formats can be done by defining a ``getExportFormats()``
method in your admin class::

    public function getExportFormats(): array
    {
        return ['pdf', 'html'];
    }

Customizing the query used to fetch the results
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you want to customize the query used to fetch the results for a specific admin,
you can override the ``DataSourceInterface`` class::

    final class DataSource implements DataSourceInterface
    {
        public function createIterator(ProxyQueryInterface $query, array $fields): SourceIteratorInterface
        {
            // Custom implementation
        }
    }

And then you can override the datasource set to the admin class.

.. _`the exporter bundle documentation`: https://docs.sonata-project.org/projects/sonata-project-exporter/en/2.x/
