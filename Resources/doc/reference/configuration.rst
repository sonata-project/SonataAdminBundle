Warning: this doc page is not up to date and will be removed soon.
==================================================================

This page will be removed soon, as it's content is being improved and moved to
other pages of the documentation. Please refer to each section's documentation for up-to-date
information on SonataAdminBundle configuration options.

Configuration
-------------

Configuration options

* ``security``
    * ``handler``
        * ``sonata.admin.security.handler.role`` : The default value
        * ``sonata.admin.security.handler.acl`` : Use this service if you want ACL


Full Configuration Options
--------------------------

.. configuration-block::

    .. code-block:: yaml

        sonata_admin:
            security:
                handler: sonata.admin.security.handler.role
                acl_user_manager: fos_user.user_manager # Name of the user manager service used to retrieve ACL users

            options:
                html5_validate: false # does not use html5 validation
                confirm_exit:   false # disable confirmation when quitting with unsaved changes
                use_select2:    false # disable select2
                pager_links:    5     # pager max links to display

            # set to true to persist filter settings per admin module in the user's session
            persist_filters: false

