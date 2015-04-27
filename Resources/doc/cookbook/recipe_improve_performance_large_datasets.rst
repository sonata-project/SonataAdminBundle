Improve performance of large datasets
=====================================

If your database table contains thousands of records, the database queries generated
by SonataAdmin may become very slow. Here are tips how to improve the performance of your admin.


Change default Pager to SimplePager
-------------------------------------

Default `Pager` is counting all rows in the table, so user can easily navigate
to any page in the Datagrid. But counting thousands or millions of records
can be slow operation. If you don't need to know the number of all records,
you can use `SimplePager` instead. It doesn't count all rows, but gives user only
information if there is next page or not.

To use SimplePager in your admin just define `pager_type` inside the service definition:

.. configuration-block::

    .. code-block:: xml

       <!-- Acme/DemoBundle/Resources/config/admin.xml -->
       <container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services/services-1.0.xsd">
           <services>
              <service id="sonata.admin.post" class="Acme\DemoBundle\Admin\PostAdmin">
                 <tag name="sonata.admin" manager_type="orm" group="Content" label="Post" pager_type="simple" />
                 <argument />
                 <argument>Acme\DemoBundle\Entity\Post</argument>
                 <argument />
             </service>
          </services>
       </container>


    .. code-block:: yaml

       # Acme/DemoBundle/Resources/config/admin.yml
       services:
           sonata.admin.post:
               class: Acme\DemoBundle\Admin\PostAdmin
               tags:
                   - { name: sonata.admin, manager_type: orm, group: "Content", label: "Post", pager_type: "simple" }
               arguments:
                   - ~
                   - Acme\DemoBundle\Entity\Post
                   - ~


.. note:: The `pager_results` template is automatically changed to `SonataAdminBundle:Pager:simple_pager_results.html.twig` if it's not already overloaded.
