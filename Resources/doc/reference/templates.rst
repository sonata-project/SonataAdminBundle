Templates
=========

By default, an Admin class used a set of templates, it is possible to tweak the default values by editing the configuration

.. code-block:: yaml

    sonata_admin:
        templates:
            # default global templates
            layout:  SonataAdminBundle::standard_layout.html.twig
            ajax:    SonataAdminBundle::ajax_layout.html.twig

            # default value if done set, actions templates, should extends a global templates
            list:    SonataAdminBundle:CRUD:list.html.twig
            show:    SonataAdminBundle:CRUD:show.html.twig
            edit:    SonataAdminBundle:CRUD:edit.html.twig
            history:  SonataAdminBundle:CRUD:history.html.twig


Usage of each template :

* layout : based layout used by the dashboard and an admin class
* ajax : default layout used when an ajax request is performed
* list : the template to use for the list action
* show : the template to use for the show action
* edit : the template to use for the edit and create action
* history : the template to use for the history / audit action

The default values will be set only if the ``Admin::setTemplates`` is not called by the Container.

