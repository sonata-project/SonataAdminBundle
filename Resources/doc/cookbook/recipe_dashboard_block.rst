Creating a Dashboard block
==============================

This is a walkthrough of how to create a dashboard block that can be used with Sonata Admin Bundle

The recipe
----------

In order to create a dashboard block, we need to:

- Create a new block class that implements BlockBundleInterface
- Create a new block template
- Create a new block service for your block
- Add the new service to the Sonata Block Bundle configuration
- Add the new service to the Sonata Admin Bundle configuration
- Verify that the block works as expected

Step 1
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

First ...


.. code-block:: php

    <?php
    // src/Acme/DemoBundle/Controller/CRUDController.php

    namespace Acme\DemoBundle\Controller;


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

Step 2
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

Step 3
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You need to tell SonataAdmin how to render your new action. You do that by creating a ``list__action_clone.html.twig`` in the
namespace of your custom Admin Controller.

.. code-block:: html+jinja

    {# src/Acme/DemoBundle/Resources/views/CRUD/list__action_clone.html.twig #}

    <a class="btn btn-sm" href="{{ admin.generateObjectUrl('clone', object) }}">clone</a>

Right now ``clone`` is not a known route, we define it in the next step.

