Creating a Custom Admin Action
==============================

This is a full working example of creating a custom list action for SonataAdmin.
The example is based on an existing ``CarAdmin`` class in an ``AppBundle``.
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

First you need to create your own Controller extending the one from SonataAdmin

.. code-block:: php

    <?php
    // src/AppBundle/Controller/CRUDController.php

    namespace AppBundle\Controller;

    use Sonata\AdminBundle\Controller\CRUDController as Controller;

    class CRUDController extends Controller
    {
        // ...
    }

Admin classes by default use the ``SonataAdmin:CRUD`` controller, this is the third parameter
of an admin service definition, you need to change it to your own.

Register the Admin as a Service
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Either by using XML:

.. code-block:: xml

        <!-- src/AppBundle/Resources/config/admin.xml -->

        <service id="app.admin.car" class="AppBundle\Admin\CarAdmin">
            <tag name="sonata.admin" manager_type="orm" group="Demo" label="Car" />
            <argument />
            <argument>AppBundle\Entity\Car</argument>
            <argument>AppBundle:CRUD</argument>
        </service>

or by adding it to your ``admin.yml``:

.. code-block:: yaml

    # src/AppBundle/Resources/config/admin.yml

    services:
        app.admin.car:
            class: AppBundle\Admin\CarAdmin
            tags:
                - { name: sonata.admin, manager_type: orm, group: Demo, label: Car }
            arguments:
                - null
                - AppBundle\Entity\Car
                - AppBundle:CRUD

For more information about service configuration please refer to Step 3 of :doc:`../reference/getting_started`

Create the custom action in your Controller
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Now it is time to actually create your custom action here, for this example I chose
to implement a ``clone`` action.

.. code-block:: php

    <?php
    // src/AppBundle/Controller/CRUDController.php

    namespace AppBundle\Controller;

    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    use Sonata\AdminBundle\Controller\CRUDController as Controller;
    use Symfony\Component\HttpFoundation\RedirectResponse;

    class CRUDController extends Controller
    {
        public function cloneAction()
        {
            $object = $this->admin->getSubject();

            if (!$object) {
                throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
            }

            // Be careful, you may need to overload the __clone method of your object
            // to set its id to null !
            $clonedObject = clone $object;

            $clonedObject->setName($object->getName().' (Clone)');

            $this->admin->create($clonedObject);

            $this->addFlash('sonata_flash_success', 'Cloned successfully');

            return new RedirectResponse($this->admin->generateUrl('list'));

            // if you have a filtered list and want to keep your filters after the redirect
            // return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
        }
    }

Here we first get the id of the object, see if it exists then clone it and insert the clone
as a new object. Finally we set a flash message indicating success and redirect to the list view.

If you want to add the current filter parameters to the redirect url you can add them to the `generateUrl` method:

.. code-block:: php

    return new RedirectResponse($this->admin->generateUrl('list', array('filter' => $this->admin->getFilterParameters())));

Using template in new controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you want to render something here you can create new template anywhere, extend sonata layout
and use `sonata_admin_content` block.

.. code-block:: html+jinja

    {% extends 'SonataAdminBundle::standard_layout.html.twig' %}

    {% block sonata_admin_content %}
        Your content here
    {% endblock %}

Create a template for the new action
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You need to tell SonataAdmin how to render your new action. You do that by
creating a ``list__action_clone.html.twig`` in the namespace of your custom
Admin Controller.

.. code-block:: html+jinja

    {# src/AppBundle/Resources/views/CRUD/list__action_clone.html.twig #}

    <a class="btn btn-sm" href="{{ admin.generateObjectUrl('clone', object) }}">clone</a>

Right now ``clone`` is not a known route, we define it in the next step.


Bringing it all together
^^^^^^^^^^^^^^^^^^^^^^^^

What is left now is actually adding your custom action to the admin class.

You have to add the new route in ``configureRoutes``:

.. code-block:: php

    // ...
    use Sonata\AdminBundle\Route\RouteCollection;

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('clone', $this->getRouterIdParameter().'/clone');
    }

This gives us a route like ``../admin/app/car/1/clone``.
You could also just write ``$collection->add('clone');`` to get a route like ``../admin/app/car/clone?id=1``

Next we have to add the action in ``configureListFields`` specifying the template we created.

.. code-block:: php

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper

             // other fields...

            ->add('_action', 'actions', array(
                'actions' => array(

                    // ...

                    'clone' => array(
                        'template' => 'AppBundle:CRUD:list__action_clone.html.twig'
                    )
                )
            ))
        ;
    }


The full ``CarAdmin.php`` example looks like this:

.. code-block:: php

    <?php
    // src/AppBundle/Admin/CarAdmin.php

    namespace AppBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Route\RouteCollection;
    use Sonata\AdminBundle\Show\ShowMapper;

    class CarAdmin extends Admin
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
                ->add('_action', 'actions', array(
                    'actions' => array(
                        'show' => array(),
                        'edit' => array(),
                        'delete' => array(),
                        'clone' => array(
                            'template' => 'AppBundle:CRUD:list__action_clone.html.twig'
                        )
                    )
                ));
        }

        protected function configureShowFields(ShowMapper $showMapper)
        {
            // ...
        }
    }
