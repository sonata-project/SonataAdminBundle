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

        # app/config/config.yml

        sonata_admin:
            security:

                # the default value
                handler: sonata.admin.security.handler.role

                # use this service if you want ACL
                handler: sonata.admin.security.handler.acl

Full Configuration Options
--------------------------

.. configuration-block::

    .. code-block:: yaml

        # Default configuration for extension with alias: "sonata_admin"
        sonata_admin:
            security:
                handler:              sonata.admin.security.handler.noop
                information:

                    # Prototype
                    id:                   []
                admin_permissions:

                    # Defaults:
                    - CREATE
                    - LIST
                    - DELETE
                    - UNDELETE
                    - EXPORT
                    - OPERATOR
                    - MASTER
                object_permissions:

                    # Defaults:
                    - VIEW
                    - EDIT
                    - DELETE
                    - UNDELETE
                    - OPERATOR
                    - MASTER
                    - OWNER
                acl_user_manager:     null
            title:                'Sonata Admin'
            title_logo:           bundles/sonataadmin/logo_title.png
            options:
                html5_validate:       true

                # Auto order groups and admins by label or id
                sort_admins:          false
                confirm_exit:         true
                use_select2:          true
                use_icheck:           true
                use_bootlint:         false
                use_stickyforms:      true
                pager_links:          null
                form_type:            standard
                dropdown_number_groups_per_colums:  2
                title_mode:           ~ # One of "single_text"; "single_image"; "both"

                # Enable locking when editing an object, if the corresponding object manager supports it.
                lock_protection:      false

                # Enable automatic registration of annotations with JMSDiExtraBundle
                enable_jms_di_extra_autoregistration: true
            dashboard:
                groups:

                    # Prototype
                    id:
                        label:                ~
                        label_catalogue:      ~
                        icon:                 '<i class="fa fa-folder"></i>'
                        provider:             ~
                        items:
                            admin:                ~
                            label:                ~
                            route:                ~
                            route_params:         []
                        item_adds:            []
                        roles:                []
                blocks:
                    type:                 ~
                    roles:                []
                    settings:

                        # Prototype
                        id:                   ~
                    position:             right
                    class:                col-md-4
            admin_services:
                model_manager:        null
                form_contractor:      null
                show_builder:         null
                list_builder:         null
                datagrid_builder:     null
                translator:           null
                configuration_pool:   null
                route_generator:      null
                validator:            null
                security_handler:     null
                label:                null
                menu_factory:         null
                route_builder:        null
                label_translator_strategy:  null
                pager_type:           null
                templates:
                    form:                 []
                    filter:               []
                    view:

                        # Prototype
                        id:                   ~
            templates:
                user_block:           'SonataAdminBundle:Core:user_block.html.twig'
                add_block:            'SonataAdminBundle:Core:add_block.html.twig'
                layout:               'SonataAdminBundle::standard_layout.html.twig'
                ajax:                 'SonataAdminBundle::ajax_layout.html.twig'
                dashboard:            'SonataAdminBundle:Core:dashboard.html.twig'
                search:               'SonataAdminBundle:Core:search.html.twig'
                list:                 'SonataAdminBundle:CRUD:list.html.twig'
                filter:               'SonataAdminBundle:Form:filter_admin_fields.html.twig'
                show:                 'SonataAdminBundle:CRUD:show.html.twig'
                show_compare:         'SonataAdminBundle:CRUD:show_compare.html.twig'
                edit:                 'SonataAdminBundle:CRUD:edit.html.twig'
                preview:              'SonataAdminBundle:CRUD:preview.html.twig'
                history:              'SonataAdminBundle:CRUD:history.html.twig'
                acl:                  'SonataAdminBundle:CRUD:acl.html.twig'
                history_revision_timestamp:  'SonataAdminBundle:CRUD:history_revision_timestamp.html.twig'
                action:               'SonataAdminBundle:CRUD:action.html.twig'
                select:               'SonataAdminBundle:CRUD:list__select.html.twig'
                list_block:           'SonataAdminBundle:Block:block_admin_list.html.twig'
                search_result_block:  'SonataAdminBundle:Block:block_search_result.html.twig'
                short_object_description:  'SonataAdminBundle:Helper:short-object-description.html.twig'
                delete:               'SonataAdminBundle:CRUD:delete.html.twig'
                batch:                'SonataAdminBundle:CRUD:list__batch.html.twig'
                batch_confirmation:   'SonataAdminBundle:CRUD:batch_confirmation.html.twig'
                inner_list_row:       'SonataAdminBundle:CRUD:list_inner_row.html.twig'
                outer_list_rows_mosaic:  'SonataAdminBundle:CRUD:list_outer_rows_mosaic.html.twig'
                outer_list_rows_list:  'SonataAdminBundle:CRUD:list_outer_rows_list.html.twig'
                outer_list_rows_tree:  'SonataAdminBundle:CRUD:list_outer_rows_tree.html.twig'
                base_list_field:      'SonataAdminBundle:CRUD:base_list_field.html.twig'
                pager_links:          'SonataAdminBundle:Pager:links.html.twig'
                pager_results:        'SonataAdminBundle:Pager:results.html.twig'
                tab_menu_template:    'SonataAdminBundle:Core:tab_menu_template.html.twig'
                knp_menu_template:    'SonataAdminBundle:Menu:sonata_menu.html.twig'
            assets:
                stylesheets:

                    # Defaults:
                    - bundles/sonatacore/vendor/bootstrap/dist/css/bootstrap.min.css
                    - bundles/sonatacore/vendor/components-font-awesome/css/font-awesome.min.css
                    - bundles/sonatacore/vendor/ionicons/css/ionicons.min.css
                    - bundles/sonataadmin/vendor/admin-lte/dist/css/AdminLTE.min.css
                    - bundles/sonataadmin/vendor/admin-lte/dist/css/skins/skin-black.min.css
                    - bundles/sonataadmin/vendor/iCheck/skins/square/blue.css
                    - bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css
                    - bundles/sonataadmin/vendor/jqueryui/themes/base/jquery-ui.css
                    - bundles/sonatacore/vendor/select2/select2.css
                    - bundles/sonatacore/vendor/select2-bootstrap-css/select2-bootstrap.min.css
                    - bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css
                    - bundles/sonataadmin/css/styles.css
                    - bundles/sonataadmin/css/layout.css
                    - bundles/sonataadmin/css/tree.css
                    - bundles/sonataadmin/css/colors.css
                javascripts:

                    # Defaults:
                    - bundles/sonatacore/vendor/jquery/dist/jquery.min.js
                    - bundles/sonataadmin/vendor/jquery.scrollTo/jquery.scrollTo.min.js
                    - bundles/sonatacore/vendor/moment/min/moment.min.js
                    - bundles/sonataadmin/vendor/jqueryui/ui/minified/jquery-ui.min.js
                    - bundles/sonataadmin/vendor/jqueryui/ui/minified/i18n/jquery-ui-i18n.min.js
                    - bundles/sonatacore/vendor/bootstrap/dist/js/bootstrap.min.js
                    - bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js
                    - bundles/sonataadmin/vendor/jquery-form/jquery.form.js
                    - bundles/sonataadmin/jquery/jquery.confirmExit.js
                    - bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js
                    - bundles/sonatacore/vendor/select2/select2.min.js
                    - bundles/sonataadmin/vendor/admin-lte/dist/js/app.min.js
                    - bundles/sonataadmin/vendor/iCheck/icheck.min.js
                    - bundles/sonataadmin/vendor/slimScroll/jquery.slimscroll.min.js
                    - bundles/sonataadmin/vendor/waypoints/lib/jquery.waypoints.min.js
                    - bundles/sonataadmin/vendor/waypoints/lib/shortcuts/sticky.min.js
                    - bundles/sonataadmin/Admin.js
                    - bundles/sonataadmin/treeview.js
            extensions:

                # Prototype
                id:
                    admins:               []
                    excludes:             []
                    implements:           []
                    extends:              []
                    instanceof:           []
                    uses:                 []
            persist_filters:      false
            show_mosaic_button:   true
