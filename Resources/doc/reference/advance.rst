Advance
=======

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
router_builder                sonata.admin.route.path_info
label_translator_strategy     sonata.admin.label.strategy.form_component
=========================     =============================================

Note: %manager-type% is to be replaced by the manager type (orm, doctrine_mongodb...)

You have 2 ways of defining the dependencies inside a ``services.xml``.

* With a tag attribute, less verbose :

.. code-block:: xml

        <service id="acme.project.admin.security_feed" class="AcmeBundle\ProjectBundle\Admin\ProjectAdmin">
            <tag
                name="sonata.admin"
                manager_type="orm"
                group="Project"
                label="Project"
                label_translator_strategy="sonata.admin.label.strategy.native"
                router_builder="sonata.admin.route.path_info"
                />
            <argument />
            <argument>AcmeBundle\ProjectBundle\Entity\Project</argument>
            <argument />
        </service>

* With a method call, more verbose

.. code-block:: xml

        <service id="acme.project.admin.project" class="AcmeBundle\ProjectBundle\Admin\ProjectAdmin">
            <tag
                name="sonata.admin"
                manager_type="orm"
                group="Project"
                label="Project"
                />
            <argument />
            <argument>AcmeBundle\ProjectBundle\Entity\Project</argument>
            <argument />

            <call method="setLabelTranslatorStrategy">
                <argument type="service" id="sonata.admin.label.strategy.native" />
            </call>

            <call method="setRouterBuilder">
                <argument type="service" id="sonata.admin.route.path_info" />
            </call>
        </service>


If you want to modify the service that is going to be injected, add the following code to your
application's config file:

.. code-block:: yaml

    # app/config/config.yml
    admins:
        sonata_admin:                                           #method name, you can find the list in the table above
            sonata.order.admin.order:                           #id of the admin service's
                model_manager: sonata.order.admin.order.manager #id of the your service


Admin Extension
---------------

Configure the default page and ordering in the list view
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Configuring the default page and ordering column can simply be achieved by overriding
the ``datagridValues`` array property. All three keys ``_page``, ``_sort_order`` and
``_sort_by`` can be omitted.

.. code-block:: php

    <?php

    use Sonata\AdminBundle\Admin\Admin;

    class PageAdmin extends Admin
    {
        // ...

        /**
         * Default Datagrid values
         *
         * @var array
         */
        protected $datagridValues = array(
            '_page' => 1, // Display the first page (default = 1)
            '_sort_order' => 'DESC', // Descendant ordering (default = 'ASC')
            '_sort_by' => 'updated' // name of the ordered field (default = the model id field, if any)
            // the '_sort_by' key can be of the form 'mySubModel.mySubSubModel.myField'.
        );

        // ...
    }

Inherited classes
-----------------

You can manage inherited classes by injected subclasses using the service configuration.

Lets consider a base class named `Person` and its subclasses `Student` and `Teacher`:

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

You will just need to change the way forms are configured in order to take into account this new subclasses:

.. code-block:: php

    <?php

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
