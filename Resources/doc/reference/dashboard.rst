Dashboard
=========

The dashboard is the main landing page. By default the dashboard lists the
different admin areas available.
If you want to custom the dashboard, add the following code to your
application's config file::

    # app/config/config.yml
    sonata_admin:
        dashboard_groups:
    ... your config ...


Examples
--------

Set the label group & add all the default items
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

::

    # app/config/config.yml
    sonata_admin:
        dashboard_groups:
            sonata_page:
                label: Page
                items: ~

Set items group
^^^^^^^^^^^^^^^

::

    # app/config/config.yml
    sonata_admin:
        dashboard_groups:
            sonata_page:
                items:
                    - sonata.page.admin.page

Add a group with all the default items
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

::

    # app/config/config.yml
    sonata_admin:
        dashboard_groups:
            sonata_page: ~

Add some items in a group
^^^^^^^^^^^^^^^^^^^^^^^^^

::

    # app/config/config.yml
    sonata_admin:
        dashboard_groups:
            sonata_page:
                item_adds:
                    - sonata.page.admin.myitem1
                    - sonata.page.admin.myitem2





.. image:: ../images/dashboard.png
           :alt: Dashboard
           :width: 200
