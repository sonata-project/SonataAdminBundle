Creating a Custom Admin Action
==============================

This is a full working example of creating a custom list action for SonataAdmin.
The example is based on an existing ``CarAdmin`` class in a ``App`` namespace.
It is assumed you already have an admin service up and running.

The recipe
----------

SonataAdmin provides a very straight-forward way of adding your own custom actions.

To do this we need to:

- extend the ``SonataAdmin:CRUD`` Controller and tell our admin class to use it
- create the custom action in our Controller
- create a template to show the action in the list view
- add the route and the new action in the Admin class

Extending the Admin Controller
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

First you need to create your own Controller extending the one from SonataAdmin::

    // src/Controller/CarAdminController.php

    namespace App\Controller;

    use Sonata\AdminBundle\Controller\CRUDController;

    class CarAdminController extends CRUDController
    {
        // ...
    }

Admin classes by default use the ``SonataAdmin:CRUD`` controller, this is the third parameter
of an admin service definition, you need to change it to your own.

Register the Admin as a Service
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Either by using XML:

.. code-block:: xml

        <!-- config/services.xml -->

        <service id="app.admin.car" class="App\Admin\CarAdmin">
            <tag name="sonata.admin" manager_type="orm" group="Demo" label="Car"/>
            <argument/>
            <argument>App\Entity\Car</argument>
            <argument>App\Controller\CarAdminController</argument>
        </service>

or by adding it to your ``services.yaml``:

.. code-block:: yaml

    # config/services.yaml

    services:
        app.admin.car:
            class: App\Admin\CarAdmin
            tags:
                - { name: sonata.admin, manager_type: orm, group: Demo, label: Car }
            arguments:
                - ~
                - App\Entity\Car
                - App\Controller\CarAdminController

For more information about service configuration please refer to Step 3 of :doc:`../getting_started/creating_an_admin`

Create the custom action in your Controller
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Now it is time to actually create your custom action here, for this example I chose
to implement a ``clone`` action::

    // src/Controller/CarAdminController.php

    namespace App\Controller;

    use Sonata\AdminBundle\Controller\CRUDController;
    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

    class CarAdminController extends CRUDController
    {
        /**
         * @param $id
         */
        public function cloneAction($id)
        {
            $object = $this->admin->getSubject();

            if (!$object) {
                throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
            }

            // Be careful, you may need to overload the __clone method of your object
            // to set its id to null !
            $clonedObject = clone $object;

            $clonedObject->setName($object->getName().' (Clone)');

            $this->admin->create($clonedObject);

            $this->addFlash('sonata_flash_success', 'Cloned successfully');

            return new RedirectResponse($this->admin->generateUrl('list'));
        }
    }

If you want to add the current filter parameters to the redirect url you can add them to the `generateUrl` method::

    return new RedirectResponse(
        $this->admin->generateUrl('list', ['filter' => $this->admin->getFilterParameters()])
    );

Here we first get the object, see if it exists then clone it and insert the clone
as a new object. Finally we set a flash message indicating success and redirect to the list view.

.. tip::

    If you want to render something here you can create new template anywhere, extend sonata layout
    and use `sonata_admin_content` block.

    .. code-block:: html+jinja

        {% extends '@SonataAdmin/standard_layout.html.twig' %}

        {% block sonata_admin_content %}
            Your content here
        {% endblock %}

Create a template for the new action
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You need to tell SonataAdmin how to render your new action. You do that by
creating a ``list__action_clone.html.twig`` in the namespace of your custom
Admin Controller.

.. code-block:: html+jinja

    {# templates/CRUD/list__action_clone.html.twig #}

    <a class="btn btn-sm" href="{{ admin.generateObjectUrl('clone', object) }}">clone</a>

Right now ``clone`` is not a known route, we define it in the next step.

Bringing it all together
^^^^^^^^^^^^^^^^^^^^^^^^

What is left now is actually adding your custom action to the admin class.

You have to add the new route in ``configureRoutes``::

    use Sonata\AdminBundle\Route\RouteCollection;

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('clone', $this->getRouterIdParameter().'/clone');
    }

This gives us a route like ``../admin/app/car/1/clone``.
You could also just write ``$collection->add('clone');`` to get a route like ``../admin/app/car/clone?id=1``

Next we have to add the action in ``configureListFields`` specifying the template we created::

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper

             // other fields...

            ->add('_action', null, [
                'actions' => [

                    // ...

                    'clone' => [
                        'template' => '@App/CRUD/list__action_clone.html.twig'
                    ]
                ]
            ])
        ;
    }

The full ``CarAdmin.php`` example looks like this::

    // src/Admin/CarAdmin.php

    namespace App\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Route\RouteCollection;
    use Sonata\AdminBundle\Show\ShowMapper;

    final class CarAdmin extends AbstractAdmin
    {
        protected function configureRoutes(RouteCollection $collection)
        {
            $collection->add('clone', $this->getRouterIdParameter().'/clone');
        }

        protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        {
            // ...
        }

        protected function configureFormFields(FormMapper $formMapper)
        {
            // ...
        }

        protected function configureListFields(ListMapper $listMapper)
        {
            $listMapper
                ->addIdentifier('name')
                ->add('engine')
                ->add('rescueEngine')
                ->add('createdAt')
                ->add('_action', null, [
                    'actions' => [
                        'show' => [],
                        'edit' => [],
                        'delete' => [],
                        'clone' => [
                            'template' => '@App/CRUD/list__action_clone.html.twig'
                        ]
                    ]
                ]);
        }

        protected function configureShowFields(ShowMapper $showMapper)
        {
            // ...
        }
    }

.. note::

    If you want to render a custom controller action in a template by using the
    render function in twig you need to add ``_sonata_admin`` as an attribute. For
    example; ``{{ render(controller('App\\Controller\\XxxxCRUDController::comment',
    {'_sonata_admin': 'sonata.admin.xxxx' })) }}``. This has to be done because the
    moment the rendering should happen the routing, which usually sets the value of
    this parameter, is not involved at all, and then you will get an error "There is
    no _sonata_admin defined for the controller
    App\Controller\XxxxCRUDController and the current route ' '."

Custom Action without Entity
----------------------------

Creating an action that is not connected to an Entity is also possible.
Let's imagine we have an import action. We register our route::

    use Sonata\AdminBundle\Route\RouteCollection;

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('import');
    }

and the controller action::

    // src/Controller/CarAdminController.php

    namespace App\Controller;

    use Sonata\AdminBundle\Controller\CRUDController;
    use Symfony\Component\HttpFoundation\Request;

    class CarAdminController extends CRUDController
    {
        public function importAction(Request $request)
        {
            //do your import logic
        }

Now, instead of adding the action to the form mapper, we can add it next to
the add button. In your admin class, overwrite the ``configureActionButtons``
method::

    public function configureActionButtons($action, $object = null)
    {
        $list = parent::configureActionButtons($action, $object);

        $list['import']['template'] = 'import_button.html.twig';

        return $list;
    }

Create a template for that button:

.. code-block:: html+jinja

    <li>
        <a class="sonata-action-element" href="{{ admin.generateUrl('import') }}">
            <i class="fa fa-level-up"></i>{{ 'import_action'|trans({}, 'SonataAdminBundle') }}
        </a>
    </li>

You can also add this action to your dashboard actions, you have to overwrite
the ``getDashboardActions`` method in your admin class and there are two
ways you can add action::

    public function getDashboardActions()
    {
        $actions = parent::getDashboardActions();

        $actions['import']['template'] = 'import_dashboard_button.html.twig';

        return $actions;
    }

Create a template for that button:

.. code-block:: html+jinja

    <a class="btn btn-link btn-flat" href="{{ admin.generateUrl('import') }}">
        <i class="fa fa-level-up"></i>{{ 'import_action'|trans({}, 'SonataAdminBundle') }}
    </a>

Or you can just pass values as array::

    public function getDashboardActions()
    {
        $actions = parent::getDashboardActions();

        $actions['import'] = [
            'label' => 'import_action',
            'translation_domain' => 'SonataAdminBundle',
            'url' => $this->generateUrl('import'),
            'icon' => 'level-up',
        ];

        return $actions;
    }
