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

.. configuration-block::

    .. code-block:: yaml

        # Default configuration for extension with alias: "sonata_admin"
        sonata_admin:
            security:
                handler:              sonata.admin.security.handler.noop

                role_admin: ROLE_ADMIN
                role_super_admin: ROLE_SUPER_ADMIN

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
                acl_user_manager: null
            title: 'Sonata Admin'
            title_logo: bundles/sonataadmin/logo_title.png
            search: true
            options:
                html5_validate: true

                # Auto order groups and admins by label or id
                sort_admins: false
                confirm_exit: true
                js_debug: false
                use_select2: true
                use_icheck: true
                use_bootlint: false
                use_stickyforms: true
                pager_links: null
                form_type: standard
                default_group: default
                default_label_catalogue: SonataAdminBundle
                default_icon: '<i class="fa fa-folder"></i>'
                dropdown_number_groups_per_colums:  2
                title_mode: ~ # One of "single_text"; "single_image"; "both"

                # Enable locking when editing an object, if the corresponding object manager supports it.
                lock_protection: false

                # Enable automatic registration of annotations with JMSDiExtraBundle
                enable_jms_di_extra_autoregistration: true
            dashboard:
                groups:

                    # Prototype
                    id:
                        label: ~
                        label_catalogue: ~
                        icon: ~
                        provider: ~
                        items:
                            admin: ~
                            label: ~
                            route: ~
                            route_params: []
                        item_adds: []
                        roles: []
                blocks:
                    type: ~
                    roles: []
                    settings:

                        # Prototype
                        id: ~
                    position: right
                    class: col-md-4
            admin_services:
                model_manager: null
                form_contractor: null
                show_builder: null
                list_builder: null
                datagrid_builder: null
                translator: null
                configuration_pool: null
                route_generator: null
                validator: null
                security_handler: null
                label: null
                menu_factory: null
                route_builder: null
                label_translator_strategy: null
                pager_type: null
                templates:
                    form: []
                    filter: []
                    view:

                        # Prototype
                        id: ~
            templates:
                user_block: '@SonataAdmin/Core/user_block.html.twig'
                add_block: '@SonataAdmin/Core/add_block.html.twig'
                layout: '@SonataAdmin/standard_layout.html.twig'
                ajax: '@SonataAdmin/ajax_layout.html.twig'
                dashboard: '@SonataAdmin/Core/dashboard.html.twig'
                search: '@SonataAdmin/Core/search.html.twig'
                list: '@SonataAdmin/CRUD/list.html.twig'
                filter: '@SonataAdmin/Form/filter_admin_fields.html.twig'
                show: '@SonataAdmin/CRUD/show.html.twig'
                show_compare: '@SonataAdmin/CRUD/show_compare.html.twig'
                edit: '@SonataAdmin/CRUD/edit.html.twig'
                preview: '@SonataAdmin/CRUD/preview.html.twig'
                history: '@SonataAdmin/CRUD/history.html.twig'
                acl: '@SonataAdmin/CRUD/acl.html.twig'
                history_revision_timestamp: '@SonataAdmin/CRUD/history_revision_timestamp.html.twig'
                action: '@SonataAdmin/CRUD/action.html.twig'
                select: '@SonataAdmin/CRUD/list__select.html.twig'
                list_block: '@SonataAdmin/Block/block_admin_list.html.twig'
                search_result_block: '@SonataAdmin/Block/block_search_result.html.twig'
                short_object_description: '@SonataAdmin/Helper/short-object-description.html.twig'
                delete: '@SonataAdmin/CRUD/delete.html.twig'
                batch: '@SonataAdmin/CRUD/list__batch.html.twig'
                batch_confirmation: '@SonataAdmin/CRUD/batch_confirmation.html.twig'
                inner_list_row: '@SonataAdmin/CRUD/list_inner_row.html.twig'
                outer_list_rows_mosaic: '@SonataAdmin/CRUD/list_outer_rows_mosaic.html.twig'
                outer_list_rows_list: '@SonataAdmin/CRUD/list_outer_rows_list.html.twig'
                outer_list_rows_tree: '@SonataAdmin/CRUD/list_outer_rows_tree.html.twig'
                base_list_field: '@SonataAdmin/CRUD/base_list_field.html.twig'
                pager_links: '@SonataAdmin/Pager/links.html.twig'
                pager_results: '@SonataAdmin/Pager/results.html.twig'
                tab_menu_template: '@SonataAdmin/Core/tab_menu_template.html.twig'
                knp_menu_template: '@SonataAdmin/Menu/sonata_menu.html.twig'
            assets:
                stylesheets:

                    # The default stylesheet list:
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

                # stylesheet paths to add to the page in addition to the list above
                extra_stylesheets: []

                # stylesheet paths to remove from the page
                remove_stylesheets: []

                javascripts:

                    # The default javascript list:
                    - 'bundles/sonatacore/vendor/jquery/dist/jquery.min.js'
                    - 'bundles/sonataadmin/vendor/jquery.scrollTo/jquery.scrollTo.min.js'
                    - 'bundles/sonataadmin/vendor/jqueryui/ui/minified/jquery-ui.min.js'
                    - 'bundles/sonataadmin/vendor/jqueryui/ui/minified/i18n/jquery-ui-i18n.min.js'
                    - 'bundles/sonatacore/vendor/moment/min/moment.min.js'
                    - 'bundles/sonatacore/vendor/bootstrap/dist/js/bootstrap.min.js'
                    - 'bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js'
                    - 'bundles/sonataadmin/vendor/jquery-form/jquery.form.js'
                    - 'bundles/sonataadmin/jquery/jquery.confirmExit.js'
                    - 'bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js'
                    - 'bundles/sonatacore/vendor/select2/select2.min.js'
                    - 'bundles/sonataadmin/vendor/admin-lte/dist/js/app.min.js'
                    - 'bundles/sonataadmin/vendor/iCheck/icheck.min.js'
                    - 'bundles/sonataadmin/vendor/slimScroll/jquery.slimscroll.min.js'
                    - 'bundles/sonataadmin/vendor/waypoints/lib/jquery.waypoints.min.js'
                    - 'bundles/sonataadmin/vendor/waypoints/lib/shortcuts/sticky.min.js'
                    - 'bundles/sonataadmin/vendor/readmore-js/readmore.min.js'
                    - 'bundles/sonataadmin/vendor/masonry/dist/masonry.pkgd.min.js'
                    - 'bundles/sonataadmin/Admin.js'
                    - 'bundles/sonataadmin/treeview.js'
                    - 'bundles/sonataadmin/sidebar.js'

                # javascript paths to add to the page in addition to the list above
                extra_javascripts: []

                # javascript paths to remove from the page
                remove_javascripts: []

            extensions:

                # Prototype
                id:
                    admins: []
                    excludes: []
                    implements: []
                    extends: []
                    instanceof: []
                    uses: []
            persist_filters: false
            filter_persister: sonata.admin.filter_persister.session
            show_mosaic_button: true
            global_search:
                show_empty_boxes: show
                case_sensitive: true
