Overwrite Admin Configuration
=============================

Sometimes you might want to overwrite some Admin settings from vendors.
This recipe will explain how to achieve this operation. However, keep
in mind this operation is quite dangerous and might break code.

From the configuration file, you can add a new section named ``default_admin_services``
with the following templates:

.. code-block:: yaml

    sonata_admin:
        default_admin_services:
            # service configuration
            model_manager:              sonata.admin.manager.orm
            data_source:                sonata.admin.data_source.orm
            field_description_factory:  sonata.admin.field_description_factory.orm
            form_contractor:            sonata.admin.builder.orm_form
            show_builder:               sonata.admin.builder.orm_show
            list_builder:               sonata.admin.builder.orm_list
            datagrid_builder:           sonata.admin.builder.orm_datagrid
            translator:                 translator
            configuration_pool:         sonata.admin.pool
            route_generator:            sonata.admin.route.default_generator
            security_handler:           sonata.admin.security.handler
            menu_factory:               knp_menu.factory
            route_builder:              sonata.admin.route.path_info
            label_translator_strategy:  sonata.admin.label.strategy.native
            pager_type:                 default

With these settings you will be able to change default services and templates used by the admin instances.

If you need to override the service of a specific admin, you can do it during the service declaration:

.. code-block:: yaml

    # config/services.yaml

    services:
        admin.blog_post:
            class: App\Admin\BlogPostAdmin
            arguments: [~, App\Entity\BlogPost, ~]
            tags:
                - name: sonata.admin
                  manager_type: orm
                  label: 'Blog post'
                  label_translator_strategy: sonata.admin.label.strategy.native
                  route_builder: sonata.admin.route.path_info
                  pager_type: simple
                  # and so on
