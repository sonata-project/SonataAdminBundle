Search
======

The admin comes with a basic global search available in the upper navigation menu. The search iterates over admin class
and look for filter with the option ``global_search`` set to true. If you are using the ``SonataDoctrineORMBundle``
any text filter will be set to ``true`` by default.


Customization
-------------

The main action is using the template ``SonataAdminBundle:Core:search.html.twig``. And each search is handled by a
``block``, the template for the block is ``SonataAdminBundle:Block:block_search_result.html.twig``.

The default template values can be configured in the configuration section

.. configuration-block::

    .. code-block:: yaml

        sonata_admin:
            templates:
                # other configuration options
                search:              SonataAdminBundle:Core:search.html.twig
                search_result_block: SonataAdminBundle:Block:block_search_result.html.twig
                
You also need to configure the block in the sonata block config

.. configuration-block::

    .. code-block:: yaml

        sonata_block:
            blocks:
                sonata.admin.block.search_result:
                contexts: [admin]

You can also configure the block template per admin while defining the admin:

.. configuration-block::

    .. code-block:: xml

        <service id="sonata.admin.post" class="Acme\DemoBundle\Admin\PostAdmin">
              <tag name="sonata.admin" manager_type="orm" group="Content" label="Post"/>
              <argument />
              <argument>Acme\DemoBundle\Entity\Post</argument>
              <argument />
              <call method="setTemplate">
                  <argument>search_result_block</argument>
                  <argument>SonataPostBundle:Block:block_search_result.html.twig</argument>
              </call>
          </service>


Performance
-----------

The current implementation can be expensive if you have a lot of entities as the resulting query does a ``LIKE %query% OR LIKE %query%``...
There is a work in progress to use an async javascript solution to better load data from the database.
