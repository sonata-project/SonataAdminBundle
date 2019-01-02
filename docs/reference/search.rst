Search
======

The admin comes with a basic global search available in the upper navigation menu. The search iterates over admin classes
and look for filter with the option ``global_search`` set to true. If you are using the ``SonataDoctrineORMBundle``
any text filter will be set to ``true`` by default.

Customization
-------------

The main action is using the template ``@SonataAdmin/Core/search.html.twig``. And each search is handled by a
``block``, the template for the block is ``@SonataAdmin/Block/block_search_result.html.twig``.

The default template values can be configured in the configuration section

.. configuration-block::

    .. code-block:: yaml

        # config/packages/sonata_admin.yaml

        sonata_admin:
            templates:
                # other configuration options
                search:              '@SonataAdmin/Core/search.html.twig'
                search_result_block: '@SonataAdmin/Block/block_search_result.html.twig'

.. note::

    If you are not using Symfony Flex, this should be added to ``app/config/config.yml``.

You also need to configure the block in the sonata block config

.. configuration-block::

    .. code-block:: yaml

        # config/packages/sonata_admin.yaml

        sonata_block:
            blocks:
                sonata.admin.block.search_result:
                    contexts: [admin]

.. note::

    If you are not using Symfony Flex, this should be added to ``app/config/config.yml``.

You can also configure the block template per admin while defining the admin:

.. configuration-block::

    .. code-block:: xml

        <service id="app.admin.post" class="App\Admin\PostAdmin">
              <tag name="sonata.admin" manager_type="orm" group="Content" label="Post" />
              <argument />
              <argument>App\Entity\Post</argument>
              <argument />
              <call method="setTemplate">
                  <argument>search_result_block</argument>
                  <argument>@SonataPost/Block/block_search_result.html.twig</argument>
              </call>
          </service>

Configure the default search result action
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

In general the search result generates a link to the edit action of an item or is using the show action, if the edit
route is disabled or you haven't the required permission. You can change this behaviour by overriding the
``searchResultActions`` property. The defined action list will we checked successive until a route with the required
permissions exists. If no route is found, the item will be displayed as a text.

.. code-block:: php

    <?php
    // src/Admin/PersonAdmin.php

    final class PersonAdmin extends AbstractAdmin
    {
        // ...

        protected $searchResultActions = ['edit', 'show'];

        // ...
    }

Performance
-----------

The current implementation can be expensive if you have a lot of entities as the resulting query does a ``LIKE %query% OR LIKE %query%``...

.. note::

    There is a work in progress to use an async JavaScript solution to better load data from the database.

Customize visibility of empty result boxes
------------------------------------------

By default all the admin boxes are shown in search results and it looks like this:

.. figure:: ../images/empty_boxes_show.png
    :align: center
    :alt: Custom view
    :width: 700px

We can fade out the boxes that have no results with:

.. code-block:: yaml

    # config/packages/sonata_admin.yaml

    sonata_admin:
        global_search:
            empty_boxes: fade

.. note::

    If you are not using Symfony Flex, this should be added to ``app/config/config.yml``.

and it looks like this:

.. figure:: ../images/empty_boxes_fade.png
    :align: center
    :alt: Custom view
    :width: 700px

The third option is to hide the empty boxes:

.. code-block:: yaml

    # config/packages/sonata_admin.yaml

    sonata_admin:
        global_search:
            empty_boxes: hide

.. note::

    If you are not using Symfony Flex, this should be added to ``app/config/config.yml``.

and it looks like this:

.. figure:: ../images/empty_boxes_hide.png
    :align: center
    :alt: Custom view
    :width: 700px

Case sensitive/insensitive
--------------------------

By default all searches are done case-sensitive.

.. note::

    This will support PostgreSQL out of the box, but unless you change the collation of MySQL, MSSQL or SQLite,
    it will have no effect! They are case-insensitive by default.

To search case-insensitive use the following option:

.. code-block:: yaml

    # config/packages/sonata_admin.yaml

    sonata_admin:
        global_search:
            case_sensitive: false

Using case-insensitivity might lead to performance issues. You can find some more information
`here <hhttps://use-the-index-luke.com/sql/where-clause/functions/case-insensitive-search>`_.

Instead of searching **all** fields case-insensitive with PostgreSQL, you can use a dedicated
`CITEXT type <https://www.postgresql.org/docs/9.1/citext.html>`_ via
`opsway/doctrine-dbal-postgresql <https://github.com/opsway/doctrine-dbal-postgresql/blob/master/src/Doctrine/DBAL/Types/Citext.php>`_
and keep the `case-sensitive` option with `true`.
