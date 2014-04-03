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

Create a new block class that implements BlockBundleInterface


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


