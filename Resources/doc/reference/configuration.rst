Configuration
=============

Configuration options

* ``security_handler``
    * ``sonata.admin.security.handler.noop`` : The default value
    * ``sonata.admin.security.handler.acl`` : Use this service if you want ACL

* ``title`` : The admin's title, can be the client name for instance (default: Sonata Admin)
* ``title_logo`` : logo to use, must be an image with a height of 28px (default : /bundles/sonataadmin/logo_title.png)

Please see :ref:`templates` for more information about how to configure default templates.


Full Configuration Options
--------------------------

.. code-block:: yaml

    sonata_admin:
        security_handler: sonata.admin.security.handler.noop

        title:      Sonata Project
        title_logo: /bundles/sonataadmin/logo_title.png
        templates:
            # default global templates
            layout:  SonataAdminBundle::standard_layout.html.twig
            ajax:    SonataAdminBundle::ajax_layout.html.twig

            # default value if done set, actions templates, should extends a global templates
            list:    SonataAdminBundle:CRUD:list.html.twig
            show:    SonataAdminBundle:CRUD:show.html.twig
            edit:    SonataAdminBundle:CRUD:edit.html.twig
