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
            history: SonataAdminBundle:CRUD:history.html.twig
            preview: SonataAdminBundle:CRUD:preview.html.twig
            delete:  SonataAdminBundle:CRUD:delete.html.twig

            # default values of helper templates
            short_object_description: SonataAdminBundle:Helper:short-object-description.html.twig

            # default values of block templates, they should extend the base_block template
            list_block: SonataAdminBundle:Block:block_admin_list.html.twig


Usage of each template :

* layout : base layout used by the dashboard and an admin class
* ajax : default layout used when an ajax request is performed
* dashboard: default layout used at the dashboard
* list : the template to use for the list action
* show : the template to use for the show action
* edit : the template to use for the edit and create action
* history : the template to use for the history / audit action
* list_block : the template used for the list of admin blocks on the dashboard
* preview : the template to use for previewing an edit / create action
* short_object_description: used to represent the entity in one-to-one/many-to-one relations
* delete: the template to use for the delete action

The default values will be set only if the ``Admin::setTemplates`` is not called by the Container.

You can easily extend the provided templates in your own and customize only the blocks you need to change:

.. code-block:: jinja

    {% extends 'SonataAdminBundle:CRUD:edit.html.twig' %}
    {# Acme/MyBundle/Resources/view/my-custom-edit.html.twig #}

    {% block title %}
        {{ "My title"|trans }}
    {% endblock%}

    {% block actions %}
         <div class="sonata-actions">
             <ul>
                 {% if admin.hasroute('list') and admin.isGranted('LIST')%}
                     <li class="btn sonata-action-element"><a href="{{ admin.generateUrl('list') }}">{{ 'link_action_list'|trans({}, 'SonataAdminBundle') }}</a></li>
                 {% endif %}
             </ul>
         </div>
    {% endblock %}


.. code-block:: php

    <?php // MyAdmin.php

    public function getTemplate($name)
    {
        switch ($name) {
            case 'edit':
                return 'AcmeMyBundle::my-custom-edit.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }
