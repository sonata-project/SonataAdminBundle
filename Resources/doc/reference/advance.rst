Advance
=======

By default services who are injected to an admin instance are

========================    =============================================
    method name             Service Id
========================    =============================================
    model_manager           sonata.admin.manager.%manager-type%
    form_contractor         sonata.admin.builder.%manager-type%_form
    show_builder            sonata.admin.builder.%manager-type%_show
    list_builder            sonata.admin.builder.%manager-type%_list
    datagrid_builder        sonata.admin.builder.%manager-type%_datagrid
    translator              translator
    configuration_pool      sonata.admin.pool
    router                  router
    validator               validator
    security_handler        sonata.admin.security.handler

Note: %manager-type% is replace by the manager type (orm, odm...)

If you want to modify the service who are going to be injected, add the following code to your
application's config file:

.. code-block:: yaml

    # app/config/config.yml
    admins:
        sonata_admin: #method name, you can find the list in the table above
            sonata.order.admin.order: #id of the admin service's
                model_manager: sonata.order.admin.order.manager #id of the your service
