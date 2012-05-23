Architecture
============

The architecture of the SonataAdminBundle is primarily inspired by the Django Admin
Project, which is truly a great project. More information can be found at the
`Django Project Website`_.

The Admin Class
---------------

The ``Admin`` class represents the CRUD definition for a specific model. It
contains all the configuration necessary to display a rich CRUD interface for
the entity.

Within the admin class, the following information can be defined:

* ``list``: The fields displayed in the list table;
* ``filter``: The fields available for filtering the list;
* ``form``: The fields used to edit the entity;
* ``show``: The fields used to show the entity;
* Batch actions: Actions that can be performed on a group of entities
  (e.g. bulk delete)

If a field is associated with another entity (and that entity also has an
``Admin`` class), then the related ``Admin`` class will be accessible from
within the first class.

The admin class is a service implementing the ``AdminInterface`` interface,
meaning that the following required dependencies are automatically injected:

* ``ListBuilder``: builds the list fields
* ``FormContractor``: builds the form using the Symfony ``FormBuilder``
* ``DatagridBuilder``: builds the filter fields
* ``Router``: generates the different urls
* ``Request``
* ``ModelManager``: Service which handles specific ORM code
* ``Translator``

Therefore, you can gain access to any service you want by injecting them into
the admin class, like so:

.. code-block:: xml

    <service id="sonata.news.admin.post" class="%sonata.news.admin.post.class%">
        <tag name="sonata.admin" manager_type="orm" group="sonata_blog" label="post"/>
        <argument />
        <argument>%sonata.news.admin.post.entity%</argument>
        <argument>%sonata.news.admin.post.controller%</argument>

        <call method="setUserManager">
            <argument type="service" id="fos_user.user_manager" />
        </call>

    </service>

Here, the FOS' User Manager is injected into the Post service.

Field Definition
----------------

A field definition is a ``FieldDescription`` object. There is one definition per list
field.

The definition contains:

* ``name``: The name of the field definition;
* ``type``: The field type;
* ``template``: The template used for displaying the field;
* ``targetEntity``: The class name of the target entity for relations;
* ``options``: Certain field types have additional options;

Template Configuration
----------------------

The current implementation uses Twig as the template engine. All templates
are located in the ``Resources/views/CRUD`` directory of the bundle. The base
template extends two layouts:

* ``AdminBundle::standard_layout.html.twig``
* ``AdminBundle::ajax_layout.html.twig``

The base templates can be configured in the Service Container. So you can easily tweak
the layout to suit your requirements.

Each field is rendered in three different ways and each has its own Twig
template. For example, for a field with a ``text`` type, the following three
templates will be used:

* ``filter_text.twig``: template used in the filter box
* ``list_text.twig``: template used in the list table

CRUDController
--------------

The controller contains the basic CRUD actions. It is related to one
``Admin`` class by mapping the controller name to the correct ``Admin``
instance.

Any or all actions can be overwritten to suit the project's requirements.

The controller uses the ``Admin`` class to construct the different actions.
Inside the controller, the ``Admin`` object is accessible through the
``configuration`` property.

Obtaining an ``Admin`` Service
------------------------------

``Admin`` definitions are accessible through the ``sonata.admin.pool`` service or
directly from the DIC (dependency injection container). The ``Admin`` definitions 
are lazy-loaded from the DIC to reduce overhead.

Declaring a new Admin class
---------------------------

Once you have created an admin class, you need to make the framework aware of
it. To do that, you need to add a tag with the name ``sonata.admin`` to the
service. Parameters for that tag are:

* ``manager_type``: Label of the database manager to use;
* ``group``: A label to allow grouping on the dashboard;
* ``label``: Label to use for the name of the entity this manager handles;

Examples:

.. code-block:: xml

    <!-- app/config/config.xml -->
    <service id="sonata.news.admin.post" class="Sonata\NewsBundle\Admin\PostAdmin">

        <tag name="sonata.admin" manager_type="orm" group="sonata_blog" label="post"/>

        <argument />
        <argument>Sonata\NewsBundle\Entity\Post</argument>
        <argument>SonataAdminBundle:CRUD</argument>
    </service>

If you want to define your own controller for handling CRUD operations, change the last argument
in the service definition to::

  <argument>SonataNewsBundle:PostAdmin</argument>

Or if you're using a YML configuration file,

.. code-block:: yaml

    services:
       sonata.news.admin.post:
          class: Sonata\NewsBundle\Admin\PostAdmin
          tags:
            - { name: sonata.admin, manager_type: orm, group: sonata_blog, label: post }
          arguments: [null, Sonata\NewsBundle\Entity\Post, SonataNewsBundle:PostAdmin]


You can extend ``Sonata\AdminBundle\Admin\Admin`` class to minimize the amount of
code to write. This base admin class uses the routing services to build routes.
Note that you can use both the Bundle:Controller format or a `service name`_ to
specify what controller to load.



.. _`Django Project Website`: http://www.djangoproject.com/
.. _`service name`: http://symfony.com/doc/2.0/cookbook/controller/service.html
