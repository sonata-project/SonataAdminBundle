Improve Performance of Large Datasets
=====================================

If your database table contains thousands of records, the database queries generated
by SonataAdmin may become very slow. Here are tips how to improve the performance of your admin.

Change default Pager to SimplePager
-----------------------------------

Default `Pager` is counting all rows in the table, so the user can navigate
to any page in the Datagrid. But counting thousands or millions of records
can be slow operation. If you don't need to know the number of all records,
you can use `SimplePager` instead. It doesn't count all rows, but gives user only
information if there is next page or not.

To use `SimplePager` in your admin,  define ``pager_type`` in the service definition:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml

        services:
            app.admin.post:
                class: App\Admin\PostAdmin
                arguments:
                    - ~
                    - App\Entity\Post
                    - ~
                tags:
                    - { name: sonata.admin, manager_type: orm, group: 'Content', label: 'Post', pager_type: 'simple' }

    .. code-block:: xml

        <!-- config/services.xml -->

        <service id="app.admin.post" class="App\Admin\PostAdmin">
            <argument/>
            <argument>App\Entity\Post</argument>
            <argument/>
            <tag name="sonata.admin" manager_type="orm" group="Content" label="Post" pager_type="simple"/>
        </service>

.. note::

    The ``pager_results`` template is automatically changed to
    ``@SonataAdmin/Pager/simple_pager_results.html.twig`` if it's not already overloaded.
