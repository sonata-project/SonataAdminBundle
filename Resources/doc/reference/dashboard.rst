Dashboard
=========

The dashboard is the main landing page. By default the dashboard lists the different admin areas available.
The admin list is a block defined by the ``sonata.admin.block.admin_list`` service. More block can be added, just
follow the instruction in the `BlockBundle documentation <http://sonata-project.org/bundles/block/master/doc/index.html>`_.

If you want to customize the dashboard, add the following code to your
application's config file:

.. code-block:: yaml

    # app/config/config.yml
    sonata_admin:
        blocks:
            # display a dashboard block
            - { position: left, type: sonata.admin.block.admin_list }

        dashboard
            groups:
                ... your config ...


Examples
--------

Set the label group & add all the default items
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: yaml

    # app/config/config.yml
    sonata_admin:
        dashboard:
            blocks:
                # display a dashboard block
                - { position: left, type: sonata.admin.block.admin_list }

            groups:
                sonata_page:
                    label: Page
                    items: ~

Set items group
^^^^^^^^^^^^^^^

.. code-block:: yaml

    # app/config/config.yml
    sonata_admin:
        dashboard:
            blocks:
                # display a dashboard block
                - { position: left, type: sonata.admin.block.admin_list }

            groups:
                sonata_page:
                    items:
                        - sonata.page.admin.page

Add a group with all the default items
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: yaml

    # app/config/config.yml
    sonata_admin:
        dashboard
            blocks:
                # display a dashboard block
                - { position: left, type: sonata.admin.block.admin_list }

            groups:
                sonata_page: ~

Add some items to a group
^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: yaml

    # app/config/config.yml
    sonata_admin:
        dashboard:
            blocks:
                # display a dashboard block
                - { position: left, type: sonata.admin.block.admin_list }

            groups:
                sonata_page:
                    item_adds:
                        - sonata.page.admin.myitem1
                        - sonata.page.admin.myitem2


.. image:: ../images/dashboard.png
           :alt: Dashboard
           :width: 200

Display two blocks with different dashboard groups
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: yaml

    # app/config/config.yml
    sonata_admin:
        dashboard
            blocks:
                # display two dashboard blocks
                - { position: left,  type: sonata.admin.block.admin_list, settings: { groups: [sonata_page1, sonata_page2] } }
                - { position: right, type: sonata.admin.block.admin_list, settings: { groups: [sonata_page3] } }

            groups:
                sonata_page1:
                    items:
                        - sonata.page.admin.myitem1
                sonata_page2:
                    items:
                        - sonata.page.admin.myitem2
                        - sonata.page.admin.myitem3
                sonata_page3:
                    items:
                        - sonata.page.admin.myitem4