Advanced configuration
======================

Service Configuration
---------------------

When you create a new Admin service you can configure its dependencies, the services which are injected by default are:

=========================     =============================================
Dependencies                  Service Id
=========================     =============================================
model_manager                 sonata.admin.manager.%manager-type%
form_contractor               sonata.admin.builder.%manager-type%_form
show_builder                  sonata.admin.builder.%manager-type%_show
list_builder                  sonata.admin.builder.%manager-type%_list
datagrid_builder              sonata.admin.builder.%manager-type%_datagrid
translator                    translator
configuration_pool            sonata.admin.pool
router                        router
validator                     validator
security_handler              sonata.admin.security.handler
menu_factory                  knp_menu.factory
route_builder                 sonata.admin.route.path_info | sonata.admin.route.path_info_slashes
label_translator_strategy     sonata.admin.label.strategy.form_component
=========================     =============================================

Note: %manager-type% is to be replaced by the manager type (orm, doctrine_mongodb...),
and the default route_builder depends on it.

You have 2 ways of defining the dependencies inside ``services.xml``:

* With a tag attribute, less verbose:

.. configuration-block::

    .. code-block:: xml

        <service id="acme.project.admin.project" class="Acme\ProjectBundle\Admin\ProjectAdmin">
            <tag
                name="sonata.admin"
                manager_type="orm"
                group="Project"
                label="Project"
                label_translator_strategy="sonata.admin.label.strategy.native"
                route_builder="sonata.admin.route.path_info"
                />
            <argument />
            <argument>Acme\ProjectBundle\Entity\Project</argument>
            <argument />
        </service>

.. configuration-block::

    .. code-block:: yaml

        acme.project.admin.project:
            class: Acme\ProjectBundle\Admin\ProjectAdmin
            tags:
                - name: sonata.admin
                  manager_type: orm
                  group: "Project"
                  label: "Project"
                  label_translator_strategy: "sonata.admin.label.strategy.native"
                  route_builder: "sonata.admin.route.path_info"
            arguments:
                - ~
                - Acme\ProjectBundle\Entity\Project
                - ~

* With a method call, more verbose

.. configuration-block::

    .. code-block:: xml

        <service id="acme.project.admin.project" class="Acme\ProjectBundle\Admin\ProjectAdmin">
            <tag
                name="sonata.admin"
                manager_type="orm"
                group="Project"
                label="Project"
                />
            <argument />
            <argument>Acme\ProjectBundle\Entity\Project</argument>
            <argument />

            <call method="setLabelTranslatorStrategy">
                <argument type="service" id="sonata.admin.label.strategy.native" />
            </call>

            <call method="setRouteBuilder">
                <argument type="service" id="sonata.admin.route.path_info" />
            </call>
        </service>

.. configuration-block::

    .. code-block:: yaml

        acme.project.admin.project:
            class: Acme\ProjectBundle\Admin\ProjectAdmin
            tags:
                - { name: sonata.admin, manager_type: orm, group: "Project", label: "Project" }
            arguments:
                - ~
                - Acme\ProjectBundle\Entity\Project
                - ~
            calls:
                - [ setLabelTranslatorStrategy, [ @sonata.admin.label.strategy.native ]]
                - [ setRouteBuilder, [ @sonata.admin.route.path_info ]]

If you want to modify the service that is going to be injected, add the following code to your
application's config file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        admins:
            sonata_admin:
                sonata.order.admin.order:   # id of the admin service this setting is for
                    model_manager:          # dependency name, from the table above
                        sonata.order.admin.order.manager  # customised service id


Creating a custom RouteBuilder
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

To create your own RouteBuilder create the PHP class and register it as a service:

* php Route Generator

.. code-block:: php

    <?php
    namespace Acme\AdminBundle\Route;

    use Sonata\AdminBundle\Builder\RouteBuilderInterface;
    use Sonata\AdminBundle\Admin\AdminInterface;
    use Sonata\AdminBundle\Route\PathInfoBuilder;
    use Sonata\AdminBundle\Route\RouteCollection;

    class EntityRouterBuilder extends PathInfoBuilder implements RouteBuilderInterface
    {
        /**
         * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
         * @param \Sonata\AdminBundle\Route\RouteCollection $collection
         */
        public function build(AdminInterface $admin, RouteCollection $collection)
        {
            parent::build($admin,$collection);
            $collection->add('yourSubAction');
            // Create button will disappear, delete functionality will be disabled as well
            // No more changes needed!
            $collection->remove('create');
            $collection->remove('delete');
        }
    }

* xml service registration

.. configuration-block::

    .. code-block:: xml

        <service id="acme.admin.route.entity" class="Acme\AdminBundle\Route\EntityRouterBuilder">
            <argument type="service" id="sonata.admin.audit.manager" />
        </service>

* YAML service registration

.. configuration-block::

    .. code-block:: yaml

        parameters:
            acme.admin.entity_route_builder.class: Acme\AdminBundle\Route\EntityRouterBuilder

        services:
            acme.admin.entity_route_builder:
                class: %acme.admin.entity_route_builder.class%
                arguments:
                    - @sonata.admin.audit.manager


Inherited classes
-----------------

You can manage inherited classes by injecting subclasses using the service configuration.

Lets consider a base class named `Person` and its subclasses `Student` and `Teacher`:

.. configuration-block::

    .. code-block:: xml

        <services>
            <service id="sonata.admin.person" class="YourNS\AdminBundle\Admin\PersonAdmin">
                <tag name="sonata.admin" manager_type="orm" group="admin" label="Person"/>
                <argument/>
                <argument>YourNS\AdminBundle\Entity\Person</argument>
                <argument></argument>
                <call method="setSubClasses">
                    <argument type="collection">
                        <argument key="student">YourNS\AdminBundle\Entity\Student</argument>
                        <argument key="teacher">YourNS\AdminBundle\Entity\Teacher</argument>
                    </argument>
                </call>
            </service>
        </services>

You will just need to change the way forms are configured in order to take into account these new subclasses:

.. code-block:: php

    <?php
    // YourNS\AdminBundle\Admin\PersonAdmin.php

    protected function configureFormFields(FormMapper $form)
    {
        $subject = $this->getSubject();

        $form->add('name');

        if ($subject instanceof Teacher) {
            $form->add('course', 'text');
        }
        elseif ($subject instanceof Student) {
            $form->add('year', 'integer');
        }
    }


Dropdowns in Tab Menu
---------------------

You can use dropdowns inside the Tab Menu by default. This can be achieved by using
the `"dropdown" => true` attribute:

.. code-block:: php

    <?php
    // YourNS\AdminBundle\Admin\PersonAdmin.php

    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        // ...other tab menu stuff

        $menu->addChild('comments', array('attributes' => array('dropdown' => true)));
        $menu['comments']->addChild('list', array('uri' => $admin->generateUrl('listComment', array('id' => $id))));
        $menu['comments']->addChild('create', array('uri' => $admin->generateUrl('addComment', array('id' => $id))));
    }


If you want to use the Tab Menu in a different way, you can replace the Menu Template:

.. configuration-block::

    .. code-block:: yaml

        sonata_admin:
            templates:
                tab_menu_template:  YourNSAdminBundle:Admin:own_tab_menu_template.html.twig


Disable content stretching
--------------------------

You can disable html, body and sidebar elements stretching. These containers are forced
to be full height by default. If you use custom layout or just don't need such behavior,
add **no-stretch** class to <html> tag.

For example:

.. code-block:: html+jinja

    {# YourNS\AdminBundle\Resources\views\standard_layout.html.twig #}
    {% block html_attributes %}class="no-js no-stretch"{% endblock %}
