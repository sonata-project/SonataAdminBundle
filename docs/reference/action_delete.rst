Deleting objects
================

This document will cover the Delete action and any related configuration options.

Basic configuration
-------------------

.. note::

    **TODO**:
    * global (yaml) options that affect the delete action
    * any Admin configuration options that affect delete
    * a note about lifecycle events triggered by delete?

Routes
~~~~~~

You can disable deleting entities by removing the corresponding routes in your Admin.
For more detailed information about routes, see :doc:`routing`::

    // src/Admin/PersonAdmin.php

    final class PersonAdmin extends AbstractAdmin
    {
        protected function configureRoutes(RouteCollectionInterface $collection): void
        {
            // Removing the delete route will disable deleting entities.
        }
    }
