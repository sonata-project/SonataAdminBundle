Configuration
=============

Configuration options

* ``security``
    * ``handler``
        * ``sonata.admin.security.handler.role`` : The default value
        * ``sonata.admin.security.handler.acl`` : Use this service if you want ACL

* ``title`` : The admin's title, can be the client name for instance (default: Sonata Admin)
* ``title_logo`` : logo to use, must be an image with a height of 28px (default : bundles/sonataadmin/logo_title.png)

Please see :doc:`templates` for more information on how to configure default templates.


Full Configuration Options
--------------------------

.. configuration-block::

    .. code-block:: yaml

        sonata_admin:
            security:
                handler: sonata.admin.security.handler.role
                acl_user_manager: fos_user.user_manager # Name of the user manager service used to retrieve ACL users

            title:      Sonata Project
            title_logo: bundles/sonataadmin/logo_title.png
            options:
                html5_validate: false # does not use html5 validation
                confirm_exit:   false # disable confirmation when quitting with unsaved changes

            templates:
                # default global templates
                layout:  SonataAdminBundle::standard_layout.html.twig
                ajax:    SonataAdminBundle::ajax_layout.html.twig
                dashboard: SonataAdminBundle:Core:dashboard.html.twig

                # default values of actions templates, they should extend global templates
                list:    SonataAdminBundle:CRUD:list.html.twig
                show:    SonataAdminBundle:CRUD:show.html.twig
                edit:    SonataAdminBundle:CRUD:edit.html.twig
                history: SonataAdminBundle:CRUD:history.html.twig
                preview: SonataAdminBundle:CRUD:preview.html.twig
                delete:  SonataAdminBundle:CRUD:delete.html.twig
                batch:   SonataAdminBundle:CRUD:list__batch.html.twig
                batch_confirmation: SonataAdminBundle:CRUD:batch_confirmation.html.twig

                # list related templates
                inner_list_row: SonataAdminBundle:CRUD:list_inner_row.html.twig
                base_list_field: SonataAdminBundle:CRUD:base_list_field.html.twig

                # default values of helper templates
                short_object_description: SonataAdminBundle:Helper:short-object-description.html.twig

                # default values of block templates, they should extend the base_block template
                list_block: SonataAdminBundle:Block:block_admin_list.html.twig

            dashboard:
                blocks:
                    # display a dashboard block
                    - { position: left, type: sonata.admin.block.admin_list }

                    # Customize this part to add new block configuration
                    - { position: right, type: sonata.block.service.text, settings: { content: "<h2>Welcome to the Sonata Admin</h2> <p>This is a <code>sonata.block.service.text</code> from the Block Bundle, you can create and add new block in these area by configuring the <code>sonata_admin</code> section.</p> <br /> For instance, here a RSS feed parser (<code>sonata.block.service.rss</code>):"} }
                    - { position: right, type: sonata.block.service.rss, settings: { title: Sonata Project's Feeds, url: http://sonata-project.org/blog/archive.rss }}

            # set to true to persist filter settings per admin module in the user's session
            persist_filters: false

        sonata_block:
            default_contexts: [cms]
            blocks:
                sonata.admin.block.admin_list:
                    contexts:   [admin]
