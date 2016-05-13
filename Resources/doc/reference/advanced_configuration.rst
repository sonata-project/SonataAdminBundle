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

.. note::

    %manager-type% is to be replaced by the manager type (orm, doctrine_mongodb...),
    and the default route_builder depends on it.

You have 2 ways of defining the dependencies inside ``services.xml``:

* With a tag attribute, less verbose:

.. configuration-block::

    .. code-block:: xml

        <service id="app.admin.project" class="AppBundle\Admin\ProjectAdmin">
            <tag
                name="sonata.admin"
                manager_type="orm"
                group="Project"
                label="Project"
                label_translator_strategy="sonata.admin.label.strategy.native"
                route_builder="sonata.admin.route.path_info"
                />
            <argument />
            <argument>AppBundle\Entity\Project</argument>
            <argument />
        </service>

.. configuration-block::

    .. code-block:: yaml

        app.admin.project:
            class: AppBundle\Admin\ProjectAdmin
            tags:
                - { name: sonata.admin, manager_type: orm, group: "Project", label: "Project", label_translator_strategy: "sonata.admin.label.strategy.native",  route_builder: "sonata.admin.route.path_info" }
            arguments:
                - ~
                - AppBundle\Entity\Project
                - ~

* With a method call, more verbose

.. configuration-block::

    .. code-block:: xml

        <service id="app.admin.project" class="AppBundle\Admin\ProjectAdmin">
            <tag
                name="sonata.admin"
                manager_type="orm"
                group="Project"
                label="Project"
                />
            <argument />
            <argument>AppBundle\Entity\Project</argument>
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

        app.admin.project:
            class: AppBundle\Admin\ProjectAdmin
            tags:
                - { name: sonata.admin, manager_type: orm, group: "Project", label: "Project" }
            arguments:
                - ~
                - AppBundle\Entity\Project
                - ~
            calls:
                - [ setLabelTranslatorStrategy, [ "@sonata.admin.label.strategy.native" ]]
                - [ setRouteBuilder, [ "@sonata.admin.route.path_info" ]]

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
    namespace AppBundle\Route;

    use Sonata\AdminBundle\Builder\RouteBuilderInterface;
    use Sonata\AdminBundle\Admin\AdminInterface;
    use Sonata\AdminBundle\Route\PathInfoBuilder;
    use Sonata\AdminBundle\Route\RouteCollection;

    class EntityRouterBuilder extends PathInfoBuilder implements RouteBuilderInterface
    {
        /**
         * @param AdminInterface  $admin
         * @param RouteCollection $collection
         */
        public function build(AdminInterface $admin, RouteCollection $collection)
        {
            parent::build($admin, $collection);

            $collection->add('yourSubAction');

            // The create button will disappear, delete functionality will be disabled as well
            // No more changes needed!
            $collection->remove('create');
            $collection->remove('delete');
        }
    }

* xml service registration

.. configuration-block::

    .. code-block:: xml

        <service id="app.admin.entity_route_builder" class="AppBundle\Route\EntityRouterBuilder">
            <argument type="service" id="sonata.admin.audit.manager" />
        </service>

* YAML service registration

.. configuration-block::

    .. code-block:: yaml

        services:
            app.admin.entity_route_builder:
                class: AppBundle\Route\EntityRouterBuilder
                arguments:
                    - "@sonata.admin.audit.manager"

Inherited classes
-----------------

You can manage inherited classes by injecting subclasses using the service configuration.

Lets consider a base class named `Person` and its subclasses `Student` and `Teacher`:

.. configuration-block::

    .. code-block:: xml

        <service id="app.admin.person" class="AppBundle\Admin\PersonAdmin">
            <tag name="sonata.admin" manager_type="orm" group="admin" label="Person" />
            <argument/>
            <argument>AppBundle\Entity\Person</argument>
            <argument></argument>
            <call method="setSubClasses">
                <argument type="collection">
                    <argument key="student">AppBundle\Entity\Student</argument>
                    <argument key="teacher">AppBundle\Entity\Teacher</argument>
                </argument>
            </call>
        </service>

You will just need to change the way forms are configured in order to take into account these new subclasses:

.. code-block:: php

    <?php
    // src/AppBundle/Admin/PersonAdmin.php

    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();

        $formMapper
            ->add('name')
        ;

        if ($subject instanceof Teacher) {
            $formMapper->add('course', 'text');
        }
        elseif ($subject instanceof Student) {
            $formMapper->add('year', 'integer');
        }
    }

Tab Menu
--------

ACL
^^^

Though the route linked by a menu may be protected the Tab Menu will not automatically check the ACl for you.
The link will still appear unless you manually check it using the `isGranted` method:

.. code-block:: php

    <?php

    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        // Link will always appear even if it is protected by ACL
        $menu->addChild($this->trans('Show'), array('uri' => $admin->generateUrl('show', array('id' => $id))));

        // Link will only appear if access to ACL protected URL is granted
        if ($this->isGranted('EDIT')) {
            $menu->addChild($this->trans('Edit'), array('uri' => $admin->generateUrl('edit', array('id' => $id))));
        }
    }

Dropdowns
^^^^^^^^^

You can use dropdowns inside the Tab Menu by default. This can be achieved by using
the `'dropdown' => true` attribute:

.. code-block:: php

    <?php
    // src/AppBundle/Admin/PersonAdmin.php

    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        // other tab menu stuff ...

        $menu->addChild('comments', array('attributes' => array('dropdown' => true)));

        $menu['comments']->addChild('list', array('uri' => $admin->generateUrl('listComment', array('id' => $id))));
        $menu['comments']->addChild('create', array('uri' => $admin->generateUrl('addComment', array('id' => $id))));
    }

If you want to use the Tab Menu in a different way, you can replace the Menu Template:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        sonata_admin:
            templates:
                tab_menu_template:  AppBundle:Admin:own_tab_menu_template.html.twig

Translations
^^^^^^^^^^^^

The translation parameters and domain can be customised by using the
``translation_domain`` and ``translation_parameters`` keys of the extra array
of data associated with the item, respectively.

.. code-block:: php

    <?php
    $menuItem->setExtras(array(
        'translation_parameters' => array('myparam' => 'myvalue'),
        'translation_domain' => 'My domain',
    ));

You can also set the translation domain on the menu root, and children will
inherit it :

.. code-block:: php

    <?php
    $menu->setExtra('translation_domain', 'My domain');

Filter parameters
^^^^^^^^^^^^^^^^^

You can add or override filter parameters to the Tab Menu:

.. code-block:: php

    <?php

    use Knp\Menu\ItemInterface as MenuItemInterface;
    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Admin\AdminInterface;
    use Sonata\CoreBundle\Form\Type\EqualType;

    class DeliveryAdmin extends AbstractAdmin
    {
        protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
        {
            if (!$childAdmin && !in_array($action, array('edit', 'show', 'list'))) {
                return;
            }

            if ($action == 'list') {
                // Get current filter parameters
                $filterParameters = $this->getFilterParameters();

                // Add or override filter parameters
                $filterParameters['status'] = array(
                    'type'  => EqualType::TYPE_IS_EQUAL, // => 1
                    'value' => Delivery::STATUS_OPEN,
                );

                // Add filters to uri of tab
                $menu->addChild('List open deliveries', array('uri' => $this->generateUrl('list', array(
                    'filter' => $filterParameters,
                ))));

                return;
            }
        }
    }

The `Delivery` class is based on the `sonata_type_translatable_choice` example inside the Core's documentation:
http://sonata-project.org/bundles/core/master/doc/reference/form_types.html#sonata-type-translatable-choice


Actions Menu
------------

You can add custom items to the actions menu for a specific action by overriding the following method:

.. code-block:: php

    public function configureActionButtons($action, $object = null)
    {
        $list = parent::configureActionButtons($action, $object);

        if (in_array($action, array('show', 'edit', 'acl')) && $object) {
            $list['custom'] = array(
                'template' => 'AppBundle:Button:custom_button.html.twig',
            );
        }

        // Remove history action
        unset($list['history']);

        return $list;
    }


.. figure:: ../images/custom_action_buttons.png
   :align: center
   :alt: Custom action buttons

Disable content stretching
--------------------------

You can disable ``html``, ``body`` and ``sidebar`` elements stretching. These containers are forced
to be full height by default. If you use custom layout or just don't need such behavior,
add ``no-stretch`` class to the ``<html>`` tag.

For example:

.. code-block:: html+jinja

    {# src/AppBundle/Resources/views/standard_layout.html.twig #}

    {% block html_attributes %}class="no-js no-stretch"{% endblock %}

Custom Action Access Management
-------------------------------

You can customize the access system inside the CRUDController by adding some entries inside the  `$accessMapping` array in the linked Admin.

.. code-block:: php

    <?php
    // src/AppBundle/Admin/PostAdmin.php

    class CustomAdmin extends AbstractAdmin
    {
        protected $accessMapping = array(
            'myCustomFoo' => 'EDIT',
            'myCustomBar' => array('EDIT', 'LIST'),
        );
    }

    <?php
    // src/AppBundle/Controller/CustomCRUDController.php

    class CustomCRUDController extends CRUDController
    {
        public function myCustomFooAction()
        {
            $this->admin->checkAccess('myCustomFoo');
            // If you can't access to EDIT role for the linked admin, an AccessDeniedException will be thrown

            // ...
        }

        public function myCustomBarAction($object)
        {
            $this->admin->checkAccess('myCustomBar', $object);
            // If you can't access to EDIT AND LIST roles for the linked admin, an AccessDeniedException will be thrown

            // ...
        }

        // ...
    }

You can also fully customize how you want to handle your access management by simply overriding ``checkAccess`` function

.. code-block:: php

    <?php
    // src/AppBundle/Admin/CustomAdmin.php

    class CustomAdmin extends AbstractAdmin
    {
        public function checkAccess($action, $object = null)
        {
            $this->customAccessLogic();
        }

        // ...
    }
