Configuration // Warning: this doc page is not up to date and will be removed soon.
===================================================================================

.. note::
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

        # Default configuration for "SonataAdminBundle"
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
                acl_user_manager:     ~         # Name of the user manager service used to retrieve ACL users
            title:                Sonata Admin
            title_logo:           bundles/sonataadmin/logo_title.png
            options:
                html5_validate:                         true      # use html5 validation
                confirm_exit:                           true      # enabled confirmation when quitting with unsaved changes
                use_select2:                            true      # enable select2
                use_icheck:                             true      # enable iCheck
                pager_links:                            ~         # pager max links to display
                dropdown_number_groups_per_colums:      2         # max items per column in dropdown menu (add button in top nav)
                title_mode:           'both'                      # 'both', 'single_text' or 'single_image'
            dashboard:
                groups:

                    # Prototype
                    id:
                        label:                ~
                        label_catalogue:      ~
                        items:                []
                        item_adds:            []
                        roles:                []
                blocks:
                    type:                 ~
                    settings:

                        # Prototype
                        id:                   []
                    position:             right
            admin_services:

                # Prototype
                id:
                    model_manager:        ~
                    form_contractor:      ~
                    show_builder:         ~
                    list_builder:         ~
                    datagrid_builder:     ~
                    translator:           ~
                    configuration_pool:   ~
                    router:               ~
                    validator:            ~
                    security_handler:     ~
                    label:                ~
            templates:
                user_block:           SonataAdminBundle:Core:user_block.html.twig
                add_block:            SonataAdminBundle:Core:add_block.html.twig
                layout:               SonataAdminBundle::standard_layout.html.twig
                ajax:                 SonataAdminBundle::ajax_layout.html.twig
                dashboard:            SonataAdminBundle:Core:dashboard.html.twig
                search:               SonataAdminBundle:Core:search.html.twig
                list:                 SonataAdminBundle:CRUD:list.html.twig
                filter:               SonataAdminBundle:Form:filter_admin_fields.html.twig
                show:                 SonataAdminBundle:CRUD:show.html.twig
                show_compare:         SonataAdminBundle:CRUD:show_compare.html.twig
                edit:                 SonataAdminBundle:CRUD:edit.html.twig
                preview:              SonataAdminBundle:CRUD:preview.html.twig
                history:              SonataAdminBundle:CRUD:history.html.twig
                acl:                  SonataAdminBundle:CRUD:acl.html.twig
                history_revision_timestamp:  SonataAdminBundle:CRUD:history_revision_timestamp.html.twig
                action:               SonataAdminBundle:CRUD:action.html.twig
                select:               SonataAdminBundle:CRUD:list__select.html.twig
                list_block:           SonataAdminBundle:Block:block_admin_list.html.twig
                search_result_block:  SonataAdminBundle:Block:block_search_result.html.twig
                short_object_description:  SonataAdminBundle:Helper:short-object-description.html.twig
                delete:               SonataAdminBundle:CRUD:delete.html.twig
                batch:                SonataAdminBundle:CRUD:list__batch.html.twig
                batch_confirmation:   SonataAdminBundle:CRUD:batch_confirmation.html.twig
                inner_list_row:       SonataAdminBundle:CRUD:list_inner_row.html.twig
                base_list_field:      SonataAdminBundle:CRUD:base_list_field.html.twig
                pager_links:          SonataAdminBundle:Pager:links.html.twig
                pager_results:        SonataAdminBundle:Pager:results.html.twig
                tab_menu_template:    SonataAdminBundle:Core:tab_menu_template.html.twig

            assets:
                stylesheets:

                    # Defaults:
                    - bundles/sonataadmin/vendor/bootstrap/dist/css/bootstrap.min.css
                    - bundles/sonataadmin/vendor/AdminLTE/css/font-awesome.min.css
                    - bundles/sonataadmin/vendor/AdminLTE/css/ionicons.min.css
                    - bundles/sonataadmin/vendor/AdminLTE/css/AdminLTE.css
                    - bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css
                    - bundles/sonataadmin/vendor/jqueryui/themes/base/jquery-ui.css
                    - bundles/sonataadmin/vendor/select2/select2.css
                    - bundles/sonataadmin/vendor/select2/select2-bootstrap.css
                    - bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/css/bootstrap-editable.css
                    - bundles/sonataadmin/css/styles.css
                    - bundles/sonataadmin/css/layout.css
                javascripts:

                    # Defaults:
                    - bundles/sonataadmin/vendor/jquery/dist/jquery.min.js
                    - bundles/sonataadmin/vendor/jquery.scrollTo/jquery.scrollTo.min.js
                    - bundles/sonatacore/vendor/moment/min/moment.min.js
                    - bundles/sonataadmin/vendor/bootstrap/dist/js/bootstrap.min.js
                    - bundles/sonatacore/vendor/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js
                    - bundles/sonataadmin/vendor/jqueryui/ui/minified/jquery-ui.min.js
                    - bundles/sonataadmin/vendor/jqueryui/ui/minified/i18n/jquery-ui-i18n.min.js
                    - bundles/sonataadmin/jquery/jquery.form.js
                    - bundles/sonataadmin/jquery/jquery.confirmExit.js
                    - bundles/sonataadmin/vendor/x-editable/dist/bootstrap3-editable/js/bootstrap-editable.min.js
                    - bundles/sonataadmin/vendor/select2/select2.min.js
                    - bundles/sonataadmin/App.js
                    - bundles/sonataadmin/Admin.js

            extensions:

                # Prototype
                id:
                    admins:               []
                    excludes:             []
                    implements:           []
                    extends:              []
                    instanceof:           []
            persist_filters:      false     # set to true to persist filter settings per admin module in the user's session

