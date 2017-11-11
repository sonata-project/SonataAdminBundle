Improve performance of large datasets
=====================================

If your database table contains thousands of records, the database queries generated
by SonataAdmin may become very slow. Here are tips how to improve the performance of your admin.

Change default Pager to SimplePager
-----------------------------------

Default `Pager` is counting all rows in the table, so user can easily navigate
to any page in the Datagrid. But counting thousands or millions of records
can be slow operation. If you don't need to know the number of all records,
you can use `SimplePager` instead. It doesn't count all rows, but gives user only
information if there is next page or not.

To use `SimplePager` in your admin just define ``pager_type`` inside the service definition:

.. configuration-block::

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/admin.xml -->

        <service id="app.admin.post" class="AppBundle\Admin\PostAdmin">
            <tag name="sonata.admin" manager_type="orm" group="Content" label="Post" pager_type="simple" />
            <argument />
            <argument>AppBundle\Entity\Post</argument>
            <argument />
        </service>

    .. code-block:: yaml

        # src/AppBundle/Resources/config/admin.yml

        services:
            app.admin.post:
                class: AppBundle\Admin\PostAdmin
                tags:
                    - { name: sonata.admin, manager_type: orm, group: "Content", label: "Post", pager_type: "simple" }
                arguments:
                    - ~
                    - AppBundle\Entity\Post
                    - ~
                public: true

.. note::

    The ``pager_results`` template is automatically changed to ``SonataAdminBundle:Pager:simple_pager_results.html.twig`` if it's not already overloaded.
