Overwrite Admin Configuration
=============================

Sometime you might want to overwrite some Admin settings from vendors. This recipe will explain how to achieve this operation. However, keep in mind this operation is quite dangerous and might break code.

From the configuration file, you can add a new section named ``admin_services`` with the following templates:

.. code-block:: yaml

    sonata_admin:
        admin_services:
            id.of.admin.service:
                # service configuration
                model_manager:              sonata.admin.manager.doctrine_orm
                form_contractor:            sonata.admin.builder.doctrine_orm
                show_builder:               sonata.admin.builder.doctrine_orm
                list_builder:               sonata.admin.builder.doctrine_orm
                datagrid_builder:           sonata.admin.builder.doctrine_orm
                translator:                 translator
                configuration_pool:         sonata.admin.pool
                route_generator:            sonata.admin.route.default_generator
                validator:                  validator
                security_handler:           sonata.admin.security.handler
                menu_factory:               knp_menu.factory
                route_builder:              sonata.admin.route.path_info
                label_translator_strategy:  sonata.admin.label.strategy.native

                # templates configuration
                templates:
                    # view templates
                    view:
                        user_block:             mytemplate.twig.html
                    # form related theme templates => this feature need to be implemented by the Persistency layer of each Admin Bundle
                    form:   [ 'MyTheme.twig.html', 'MySecondTheme.twig.html']
                    filter: [ 'MyTheme.twig.html', 'MySecondTheme.twig.html']


With these settings you will be able to change default services and templates used by the `id.of.admin.service`` admin instance.
