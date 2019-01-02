Configuration
=============

.. note::

    This page will be removed soon, as it's content is being improved and moved to
    other pages of the documentation. Please refer to each section's documentation for up-to-date
    information on SonataAdminBundle configuration options.

Configuration
-------------

Configuration options

.. configuration-block::

    .. code-block:: yaml

        # config/packages/sonata_admin.yaml

        sonata_admin:
            security:

                # the default value
                handler: sonata.admin.security.handler.role

                # use this service if you want ACL
                handler: sonata.admin.security.handler.acl

Full Configuration Options
--------------------------

.. literalinclude:: ../_includes/configuration_reference.yaml
   :language: yaml
   :linenos:
