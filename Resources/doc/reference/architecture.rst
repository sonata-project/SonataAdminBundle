Architecture
============

The architecture of the bundle is primarily inspired by the Django Admin
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
* ``formGroups``: The group definition where a field must be displayed (edit form)
* Batch actions: Actions that can be performed on a group of entities
  (e.g. bulk delete)

If a field is associated with another entity (and that entity also has an
``Admin`` class), then the related ``Admin`` class will be accessible from
within the first class.

The admin class is a service implementing the ``AdminInterface`` interface,
meaning that the following required dependencies are automatically injected:

* ``ListBuilder``: builds the list fields
* ``FormContractor``: constructs the form using the Symfony ``FormBuilder``
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
* ``template``: The template to use to display the field;
* ``targetEntity``: The class name of the target entity for relations;
* ``options``: Certain field types have additional options;

Template Configuration
-----------------------

The current implementation uses Twig as the template engine. All templates
are located in the ``Resources/views/CRUD`` directory of the bundle. The base
template extends two layouts:

* ``AdminBundle::standard_layout.twig``
* ``AdminBundle::ajax_layout.twig``

The base templates can be configured in the Service Container. So you can easily tweak
the layout to suit your requirements.

Each field is rendered in three different ways and each has its own Twig
template. For example, for a field with a ``text`` type, the following three
templates will be used:

* ``edit_text.twig``: template used in the edit form
* ``filter_text.twig``: template used in the filter box
* ``list_text.twig``: template used in the list table

CrudController
--------------

The controller contains the basic CRUD actions, it controller is related to one
``Admin`` class by mapping the controller name to the correct ``Admin``
instance.

Any or all actions can be overwritten to suit the project's requirements.

The controller uses the ``Admin`` class to construct the different actions.
Inside the controller, the ``Admin`` object is accessible through the
``configuration`` property.

Obtaining an ``Admin`` Service
------------------------------

``Admin`` definitions are accessible through the 'sonata.admin.pool' service or
directly from the DIC. The ``Admin`` definitions are lazy loaded from the DIC to
reduce overhead.

Declaring a new Admin class
---------------------------

Once you have created an admin class, you must declare the class to use it. Like

.. code-block:: xml

    <!-- app/config/config.xml -->
    <service id="sonata.news.admin.post" class="Sonata\NewsBundle\Admin\PostAdmin">

        <tag name="sonata.admin" manager_type="orm" group="sonata_blog" label="post"/>

        <argument />
        <argument>Sonata\NewsBundle\Entity\Post</argument>
        <argument>SonataNewsBundle:PostAdmin</argument>
    </service>

Or if you're using a YML configuration file,

.. code-block:: yml

    services:
       sonata.news.admin.post:
          class: Sonata\NewsBundle\Admin\PostAdmin
          tags:
            - { name: sonata.admin, manager_type: orm, group: sonata_blog, label: post }
          arguments: [null, Sonata\NewsBundle\Entity\Post, SonataNewsBundle:PostAdmin]


.. _`Django Project Website`: http://www.djangoproject.com/
