Persisting Filters
==================

Persisting filters allow your application to save the filters the authenticated user has submitted.
Then the saved filters will be reused if the page is displayed again.


Enable Filters Persistence
--------------------------

By default, filters persistence is disabled.
You can enable it in your ``sonata_admin`` configuration :

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        sonata_admin:
            persist_filters: true


Choose the persistence strategy
-------------------------------

When you enable the filters persistence by setting ``persist_filters`` to ``true``.
SonataAdmin will use the default filter persister : ``Sonata\AdminBundle\Filter\Persister\SessionFilterPersister`` (which is, by now, the only one provided).

You can implement your own filter persister by creating a new class that implement the ``Sonata\AdminBundle\Filter\Persister\FilterPersisterInterface`` interface and registering it as a service.
Then the only thing to do is to tell SonataAdmin to use this service as filter persister.


Globally :

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        sonata_admin:
            persist_filters: filter_persister_service_id


Per Admin (using XML) :

.. configuration-block::

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/admin.xml -->

        <service id="app.admin.car" class="AppBundle\Admin\CarAdmin">
            <tag name="sonata.admin" manager_type="orm" group="Demo" label="Car" persist_filters="filter_persister_service_id" />
            <argument />
            <argument>AppBundle\Entity\Car</argument>
            <argument />
        </service>


Per Admin (using YAML) :

.. configuration-block::

    .. code-block:: yaml

        # src/AppBundle/Resources/config/admin.yml

        services:
            app.admin.car:
                class: AppBundle\Admin\CarAdmin
                tags:
                    - { name: sonata.admin, manager_type: orm, group: Demo, label: Car, persist_filters: filter_persister_service_id }
                arguments:
                    - null
                    - AppBundle\Entity\Car
                    - null
