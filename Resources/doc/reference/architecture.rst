Architecture
============

The architecture of the SonataAdminBundle is primarily inspired by the Django Admin
Project, which is truly a great project. More information can be found at the
`Django Project Website`_.

If you followed the instructions on the :doc:`getting_started` page, you should by
now have an Admin class and an Admin service. In this chapter, we'll discuss more in
depth how it works.

The Admin Class
---------------

The ``Admin`` class maps a specific model to the rich CRUD interface provided by
SonataAdminBundle. In other words, using your Admin classes, you can configure
what is shown by SonataAdminBundle in each CRUD action for the associated model.
By now you've seen 3 of those actions in the ``getting started`` page: list, 
filter and form (for creation/edition). However, a fully configured Admin class
can define more actions:

* ``list``: The fields displayed in the list table;
* ``filter``: The fields available for filtering the list;
* ``form``: The fields used to create/edit the entity;
* ``show``: The fields used to show the entity;
* Batch actions: Actions that can be performed on a group of entities
  (e.g. bulk delete)

The ``Sonata\AdminBundle\Admin\Admin`` class is provided as an easy way to
map your models, by extending it. However, any implementation of the 
``Sonata\AdminBundle\Admin\AdminInterface`` can be used to define an Admin
service. For each Admin service, the following required dependencies are 
automatically injected by the bundle:

* ``ModelManager``: service which handles specific ORM code
* ``FormContractor``: builds the edit/create views form using the Symfony ``FormBuilder``
* ``ShowBuilder``: builds the 'show' view
* ``ListBuilder``: builds the list fields
* ``DatagridBuilder``: builds the filter fields
* ``Translator``: generates translations
* ``ConfigurationPool``: configuration pool where all Admin class instances are stored
* ``RouterGenerator``: generates the different urls
* ``Validator``: handles model validation
* ``SecurityHandler``: handles permissions for model instances and actions
* ``MenuFactory``: generates the side menu, depending on the current action
* ``RouteBuilder``: allows you to easily add routes for new actions
* ``Request`` : http request received
* ``LabelTranslatorStrategy``: a strategy to use when generating labels 

.. note::

    Each of these dependencies is used for a specific task, briefly described above.
    If you wish to learn more about how they are used, check the respective documentation
    chapter. In most cases, you won't need to worry about their underlying implementation.


All these dependencies have default values that you can override by a using 
``call`` to the matching ``setter`` when declaring the Admin service, like so:

.. code-block:: xml

    <service id="sonata.admin.tag" class="Acme\DemoBundle\Admin\PostAdmin">
          <tag name="sonata.admin" manager_type="orm" group="Content" label="Post"/>
          <argument />
          <argument>Acme\DemoBundle\Entity\Post</argument>
          <argument />
          <call method="setLabelTranslatorStrategy">
              <argument>sonata.admin.label.strategy.underscore</argument>
          </call>
      </service>

Here, we declare the same Admin service as before, but using a different label translator strategy, replacing the default one. Notice that ``sonata.admin.label.strategy.underscore`` is a 
service provided by SonataAdminBundle, but you could just as easily use a service of your
own.

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
specify what controller to load. If you provide null instead of SonataNewsBundle:PostAdmin,
you will not need to create a controller class and the system will use the default.

.. _`Django Project Website`: http://www.djangoproject.com/
.. _`service name`: http://symfony.com/doc/2.1/cookbook/controller/service.html
