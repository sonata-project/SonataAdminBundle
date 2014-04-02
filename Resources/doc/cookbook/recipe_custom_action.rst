Creating a Custom Admin Action
==============================

This is a full working example of creating a custom list action for SonataAdmin.
The example is based on an existing ``CarAdmin`` class in an ``AcmeDemoBundle``. It is
assumed you already have an admin service up and running.

The recipe
----------

SonataAdmin provides a very straight-forward way of adding your own custom actions.

To do this we need to:

- extend the ``SonataAdmin:CRUD`` Controller and tell our admin class to use it
- create the custom action in your Controller
- create a template to show the action in the list view
- add the route and the new action in the Admin class

Extending the Admin Controller
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

First you need to create your own Controller extending the one from SonataAdmin


.. code-block:: php

    <?php
    // src/Acme/DemoBundle/Controller/CRUDController.php

    namespace Acme\DemoBundle\Controller;

    use Sonata\AdminBundle\Controller\CRUDController as Controller;

    class CRUDController extends Controller
    {
        // ...
    }

Admin classes by default use the ``SonataAdmin:CRUD`` controller, this is the third parameter
of an admin service definition, you need to change it to your own.

Either by using XML:

.. code-block:: xml

        <!-- src/Acme/DemoBundle/Resources/config/admin.xml -->
        ...

        <service id="acme.demo.admin.car" class="Acme\DemoBundle\Admin\CarAdmin">

            <tag name="sonata.admin" manager_type="orm" group="Demo" label="Car" />

            <argument />
            <argument>Acme\DemoBundle\Entity\Car</argument>
            <argument>AcmeDemoBundle:CRUD</argument>

            ...

        </service>

        ...

Or by overwriting the configuration in your ``config.yml``:

.. code-block:: yaml

    # app/config/config.yml

    services:
        acme.demo.admin.car:
            class: Acme\DemoBundle\Admin\CarAdmin
            tags:
                - { name: sonata.admin, manager_type: orm, group: Demo, label: Car }
            arguments:
                - null
                - Acme\DemoBundle\Entity\Car
                - AcmeDemoBundle:CRUD


For more information about service configuration please refer to Step 3 of :doc:`../reference/getting_started`

Create the custom action in your Controller
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Now it's time to actually create your custom action here, for this example I chose
to implement a ``clone`` action.

.. code-block:: php

    <?php // src/Acme/DemoBundle/Controller/CRUDController.php

    namespace Acme\DemoBundle\Controller;

    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    use Sonata\AdminBundle\Controller\CRUDController as Controller;
    use Symfony\Component\HttpFoundation\RedirectResponse;

    class CRUDController extends Controller
    {
        public function cloneAction()
        {
            $id = $this->get('request')->get($this->admin->getIdParameter());

            $object = $this->admin->getObject($id);

            if (!$object) {
                throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
            }

            $clonedObject = clone $object;
            $clonedObject->setName($object->getName()." (Clone)");

            $this->admin->create($clonedObject);

            $this->addFlash('sonata_flash_success', 'Cloned successfully');

            return new RedirectResponse($this->admin->generateUrl('list'));
        }
    }

Here we first get the id of the object, see if it exists then clone it and insert the clone
as new object. Finally we set a flash message indicating success and redirect to the list view.

Create a template for the new action
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You need to tell SonataAdmin how to render your new action. You do that by creating a ``list__action_clone.html.twig`` in the
namespace of your custom Admin Controller.

.. code-block:: html+jinja

    {# src/Acme/DemoBundle/Resources/views/CRUD/list__action_clone.html.twig #}

    <a class="btn btn-sm" href="{{ admin.generateObjectUrl('clone', object) }}">clone</a>

Right now ``clone`` is not a known route, we define it in the next step.


Bringing it all together
^^^^^^^^^^^^^^^^^^^^^^^^

What's left now is actually adding your custom action to the admin class.

You have to add the new route in ``configureRoutes``:

.. code-block:: php

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('clone', $this->getRouterIdParameter().'/clone');
    }

This gives us a route like ``../admin/sonata/demo/car/1/clone``.
You could also just do ``$collection->add('clone');`` to get a route like ``../admin/sonata/demo/car/clone?id=1``

Next we have to add the action in ``configureListFields`` specifying the template we created.

.. code-block:: php

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper

             // other fields...

            ->add('_action', 'actions', array(
                'actions' => array(
                    'Clone' => array(
                        'template' => 'AcmeDemoBundle:CRUD:list__action_clone.html.twig'
                    )
                )
            ))
        ;
    }


The full example ``CarAdmin.php`` looks like this:

.. code-block:: php

    <?php
    // src/Acme/DemoBundle/Admin/CarAdmin.php

    namespace Acme\DemoBundle\Admin;

    // ...

    use Sonata\AdminBundle\Route\RouteCollection;

    class CarAdmin extends Admin
    {
         // ...

        protected function configureListFields(ListMapper $listMapper)
        {
            $listMapper
                ->addIdentifier('name')
                ->add('engine')
                ->add('rescueEngine')
                ->add('createdAt')
                ->add('_action', 'actions', array(
                    'actions' => array(
                        'Clone' => array(
                            'template' => 'AcmeDemoBundle:CRUD:list__action_clone.html.twig'
                        )
                    )
                ));
        }

        protected function configureRoutes(RouteCollection $collection)
        {
            $collection->add('clone', $this->getRouterIdParameter().'/clone');
        }
    }
