Templates
=========

``SoantaAdminBundle`` comes with a significant amount of ``twig`` files used to display the
different parts of each ``Admin`` action's page. If you read the ``Templates`` part of the :doc:`architecture` section of this guide, you should know by now how these are organized in
the ``views`` folder. If you haven't, now would be a good time to do it.

Besides these, some other views files are included from the storage layer. As their content and
structure are specific to each implementation, they are not discussed here, but it's important
that you keep in mind that they exist and are as relevant as the view files included
directly in ``SonataAdminBundle``.

Global Templates
----------------

``SonataAdminBundle`` views are implemented using ``twig`` files, and take full advantage of its
inheritance capabilities. As such, even the most simple page is actually rendered using many
different ``twig`` files. At the end of that ``twig`` inheritance hierarchy is always one of two files:

* layout: SonataAdminBundle::standard_layout.html.twig
* ajax: SonataAdminBundle::ajax_layout.html.twig

As you might have guessed from their names, the first is used in 'standard' request and the other
for AJAX calls. The ``SonataAdminBundle::standard_layout.html.twig`` contains several elements which
exist across the whole page, like the logo, title, upper menu and menu. It also includes the base CSS
and JavaScript files and libraries used across the whole administration section. The AJAX template
doesn't include any of these elements.

Dashboard Template
------------------

The template used for rendering the dashboard can also be configured. See the :doc:`dashboard` page
for more information

CRUDController Actions Templates
--------------------------------

As seen before, the ``CRUDController`` has several actions that allow you to manipulate your
model instances. Each of those actions uses a specific template file to render its content.
By default, ``SonataAdminBundle`` uses the following templates for their matching action:

* list: SonataAdminBundle:CRUD:list.html.twig
* show: SonataAdminBundle:CRUD:show.html.twig
* edit: SonataAdminBundle:CRUD:edit.html.twig
* history: SonataAdminBundle:CRUD:history.html.twig
* preview: SonataAdminBundle:CRUD:preview.html.twig
* delete: SonataAdminBundle:CRUD:delete.html.twig
* batch_confirmation: SonataAdminBundle:CRUD:batch_confirmation.html.twig
* acl: SonataAdminBundle:CRUD:acl.html.twig

Notice that all these templates extend other templates, and some do only that. This inheritance
architecture is designed to help you easily make customizations by extending these templates
in your own bundle, rather than rewriting everything.

If you look closely, all of these templates ultimately extend the ``base_template`` variable that's
passed from the controller. This variable will always take the value of one of the above mentioned
global templates, and this is how changes made to those files affect all the ``SonataAdminBundle``
interface.

Row Templates
-------------

It is possible to completely change how each row of results is rendered in the
list view, by customizing the ``inner_list_row`` and ``base_list_field`` templates.
For more information about this, see the :doc:`recipe_row_templates`
cookbook entry.

Other Templates
---------------

There are several other templates that can be customized, enabling you to fine-tune
``SonataAdminBundle``:

* user_block: customizes the Twig block rendered by default in the top right corner of the admin interface, containing user information. Empty by defautl, see ``SonataUserBundle`` for a real example.
* history_revision_timestamp: customizes the way timestamps are rendered when using history related actions.
* action: a generic template you can use for your custom actions
* short_object_description: used by the ``getShortObjectDescriptionAction`` action from the ``HelperController``, this template displays a small description of a model instance.
* list_block: the template used to render the dashboard's admin mapping lists. More info on the :doc:`dashboard` page.
* batch: template used to render the checkboxes that precede each instance on list views.
* select: when loading list views as part of sonata_admin form types, this template is used to create a button that allows you to select the matching line.
* pager_links: renders the list of pages displayed at the end of the list view (when more than one page exists)
* pager_results: renders the dropdown that lets you choose the number of elements per page on list views

Configuring templates
---------------------

Like said before, the main goal of this template structure is to make it easy for you
to customize the ones you need. You can simply extend the ones you want in your own bundle,
and tell ``SonataAdminBundle`` to use your templates instead of the default ones. You can do so
in several ways.

You can specify your templates in the config.yml file, like so:

.. configuration-block::

    .. code-block:: yaml

        sonata_admin:
            templates:
                layout:  SonataAdminBundle::standard_layout.html.twig
                ajax:    SonataAdminBundle::ajax_layout.html.twig
                list:    SonataAdminBundle:CRUD:list.html.twig
                show:    SonataAdminBundle:CRUD:show.html.twig
                edit:    SonataAdminBundle:CRUD:edit.html.twig
                history: SonataAdminBundle:CRUD:history.html.twig
                preview: SonataAdminBundle:CRUD:preview.html.twig
                delete:  SonataAdminBundle:CRUD:delete.html.twig
                batch:   SonataAdminBundle:CRUD:list__batch.html.twig
                acl:     SonataAdminBundle:CRUD:acl.html.twig
                action:  SonataAdminBundle:CRUD:action.html.twig
                select:  SonataAdminBundle:CRUD:list__select.html.twig
                dashboard:           SonataAdminBundle:Core:dashboard.html.twig
                search:              SonataAdminBundle:Core:search.html.twig
                batch_confirmation:  SonataAdminBundle:CRUD:batch_confirmation.html.twig
                inner_list_row:      SonataAdminBundle:CRUD:list_inner_row.html.twig
                base_list_field:     SonataAdminBundle:CRUD:base_list_field.html.twig
                inner_list_row:      SonataAdminBundle:CRUD:list_inner_row.html.twig
                base_list_field:     SonataAdminBundle:CRUD:base_list_field.html.twig
                list_block:          SonataAdminBundle:Block:block_admin_list.html.twig
                user_block:          SonataAdminBundle:Core:user_block.html.twig
                pager_links:         SonataAdminBundle:Pager:links.html.twig
                pager_results:       SonataAdminBundle:Pager:results.html.twig
                history_revision_timestamp:  SonataAdminBundle:CRUD:history_revision_timestamp.html.twig
                short_object_description:    SonataAdminBundle:Helper:short-object-description.html.twig
                search_result_block: SonataAdminBundle:Block:block_search_result.html.twig

Notice that this is a global change, meaning it will affect all model mappings automatically,
both for ``Admin`` mappings defined by you and by other bundles.

If you wish, you can specify custom templates on a per ``Admin`` mapping basis. Internally,
the ``CRUDController`` fetches this information from the ``Admin`` class instance, so you can
specify the templates to use in the ``Admin`` service definition:

.. configuration-block::

    .. code-block:: xml

        <service id="sonata.admin.post" class="Acme\DemoBundle\Admin\PostAdmin">
              <tag name="sonata.admin" manager_type="orm" group="Content" label="Post"/>
              <argument />
              <argument>Acme\DemoBundle\Entity\Post</argument>
              <argument />
              <call method="setTemplate">
                  <argument>edit</argument>
                  <argument>AcmeDemoBundle:PostAdmin:edit.html.twig</argument>
              </call>
          </service>

    .. code-block:: yaml

        services:
            sonata.admin.post:
                class: Acme\DemoBundle\Admin\PostAdmin
                tags:
                    - { name: sonata.admin, manager_type: orm, group: "Content", label: "Post" }
                arguments:
                    - ~
                    - Acme\DemoBundle\Entity\Post
                    - ~
                calls:
                    - [ setTemplate, [edit, AcmeDemoBundle:PostAdmin:edit.html.twig]]


.. note::

    A ``setTemplates(array $templates)`` (notice the plural) function also exists, that allows
    you to set multiple templates at once. Notice that, if used outside of the service definition
    context, ``setTemplates(array $templates)`` will replace the whole template list for that
    ``Admin`` class, meaning you have to explicitly pass the full template list in the
    ``$templates`` argument.


Changes made using the ``setTemplate()`` and ``setTemplates()`` functions override the customizations
made in the configuration file, so you can specify a global custom template and then override that
customization on a specific ``Admin`` class.
