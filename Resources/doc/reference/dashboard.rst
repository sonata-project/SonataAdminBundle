Dashboard
=========

The dashboard is the main landing page. By default the dashboard lists the
different admin areas available.
If you want to custom the dashbord, add the following code to your
application's config file:
.. code-block:: yaml

    # app/config/config.yml
    sonata_admin:
        dashboard:
    ... your config ...


Some examples of config file:
1 - Set the label group
.. code-block:: yaml

    # app/config/config.yml
    sonata_admin:
        dashboard:
          sonata_page:
            label: Page
            items: ~

2 - Set items group
.. code-block:: yaml

    # app/config/config.yml
    sonata_admin:
        dashboard:
          sonata_page:
            items:
                - sonata.page.admin.page

3 - Add an item of the group
.. code-block:: yaml

    # app/config/config.yml
    sonata_admin:
        dashboard:
          sonata_page: ~


.. image:: ../images/dashboard.png
           :alt: Dashboard
           :width: 200
