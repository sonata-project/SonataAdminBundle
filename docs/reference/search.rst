Search
======

The admin comes with a basic global search available in the upper navigation menu. The search iterates over
admin classes and looks for filters implementing the ``Sonata\AdminBundle\Search\SearchableFilterInterface`` interface with
the method ``isSearchEnabled()`` returning true. If you are using ``SonataDoctrineORMBundle``, the
``Sonata\DoctrineORMAdminBundle\Filter\StringFilter`` filter is searchable and relies on a ``global_search`` option.

.. note::

    The current implementation can be expensive if you have a lot of entities
    as the resulting query does a ``LIKE %query% OR LIKE %query%``...

Disabling the search by admin
-----------------------------

You can disable the search for a whole admin by setting the ``global_search`` attribute
to ``false`` at your admin definition using the tag ``sonata.admin``.

.. configuration-block::

    .. code-block:: xml

        <service id="app.admin.post" class="App\Admin\PostAdmin">
            <tag name="sonata.admin" global_search="false" model_class="App\Entity\Post" manager_type="orm" group="Content" label="Post"/>
        </service>

Customization
-------------

Configure the search templates
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

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

You also need to configure the block in the sonata block config

.. configuration-block::

    .. code-block:: yaml

        # config/packages/sonata_admin.yaml

        sonata_block:
            blocks:
                sonata.admin.block.search_result:
                    contexts: [admin]

You can also configure the block template per admin while defining the admin:

.. configuration-block::

    .. code-block:: xml

        <service id="app.admin.post" class="App\Admin\PostAdmin">
              <tag name="sonata.admin" model_class="App\Entity\Post" manager_type="orm" group="Content" label="Post"/>
              <call method="setTemplate">
                  <argument>search_result_block</argument>
                  <argument>@SonataPost/Block/block_search_result.html.twig</argument>
              </call>
          </service>

Configure the default search result actions
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

In general the search result generates a link to the show action of an item or
displayed as a text if the show route is disabled or you haven't the required
permission. You can change this behavior by overriding the option

.. code-block:: yaml

    # config/packages/sonata_admin.yaml

    sonata_admin:
        global_search:
            admin_route: edit

Customize visibility of empty result boxes
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

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

and it looks like this:

.. figure:: ../images/empty_boxes_hide.png
    :align: center
    :alt: Custom view
    :width: 700px
