Templates
=========

By default, an Admin class uses a set of templates, it is possible to tweak the default values by editing the configuration

.. code-block:: yaml

    sonata_admin:
        templates:
            # default global templates
            layout:  SonataAdminBundle::standard_layout.html.twig
            ajax:    SonataAdminBundle::ajax_layout.html.twig

            # default values of actions templates, they should extend global templates
            list:    SonataAdminBundle:CRUD:list.html.twig
            show:    SonataAdminBundle:CRUD:show.html.twig
            edit:    SonataAdminBundle:CRUD:edit.html.twig
            history:  SonataAdminBundle:CRUD:history.html.twig

            # default block templates, these should extend the SonataBlockBundle:Block:block_base.html.twig 
            list_block: SonataAdminBundle:Block:block_admin_list.html.twig


Usage of each template :

* layout : base layout used by the dashboard and an admin class
* ajax : default layout used when an ajax request is performed
* dashboard: default layout used at the dashboard
* list : the template to use for the list action
* show : the template to use for the show action
* edit : the template to use for the edit and create action
* history : the template to use for the history / audit action
* list_block : the template used for the admin blocks on the dashboard

The default values will be set only if the ``Admin::setTemplates`` is not called by the Container.

