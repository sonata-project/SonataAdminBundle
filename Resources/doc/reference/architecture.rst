Architecture
============

The architecture of the bundle is mostly based off of the Django Admin Project,
which is a truly great project. More information can be found at the
`Django Project Website`_.

The Admin Class
---------------

The ``Admin`` class is the CRUD definition of one Doctrine entity. It contains
all the configuration necessary to display a rich CRUD for the entity. From
within an admin class, the following information can be defined:

* ``list``: The fields displayed in the list table;
* ``filter``: The fields available for filter the list;
* ``form``: The fields used to edit the entity;
* ``formGroups``: The group definition where a field must be displayed (edit form)
* The batch actions: Actions that can be performed on a group of entities
  (e.g. bulk delete)

If a field is associated with another entity (and that entity also has an
``Admin`` class), then the related ``Admin`` class will be accessible from
within the first ``Admin`` class.

The admin class is a service implementing the ``AdminInterface``, meaning that
only required dependencies are injected:

* ``ListBuilder``
* ``FormBuildre``
* ``DatagridBuilder``
* ``Router``
* ``Request``
* ``EntityManager``
* ``Translator``


Therefore, you have access to every service you want by injecting them into the
admin class, like:

.. code-block:: xml

    <service id="sonata.news.admin.post" class="%sonata.news.admin.post.class%">
        <tag name="sonata.admin" manager_type="orm" group="sonata_blog" label="post"/>
        <argument>%sonata.news.admin.post.entity%</argument>
        <argument>%sonata.news.admin.post.controller%</argument>

        <call method="setUserManager">
            <argument type="service" id="fos_user.user_manager" />
        </call>

    </service>

Here, the FOS' User Manager is injected into the Post service.


Field Definition
----------------

A field definition is a FieldDescription object. There is one definition per list
field.

The definition contains:

* ``name``: The name of the field definition;
* ``type``: The field type;
* ``template``: The template to use to display the field;
* ``targetEntity``: The class name of the target entity for relations;
* ``options``: Certain field types have additional options;

Template Configuration
~~~~~~~~~~~~~~~~~~~~~~

The current implementation uses Twig as the template engine. All templates
are located in the Resources/views/CRUD directory of the bundle. The base
template extends two layouts:

* ``AdminBundle::standard_layout.twig``
* ``AdminBundle::ajax_layout.twig``

Each field is rendered in three different ways and each has its own Twig
template. For example, for a field with a ``text`` type, the following three
templates will be used:

* ``edit_text.twig``: template used in the form edition
* ``filter_text.twig``: template used in the filter box
* ``list_text.twig``: template used in the list table

CrudController
--------------

The controller contains the basic CRUD actions, the controller is
related to one ``Admin`` class by mapping the controller name to the correct
``Admin`` instance.

All actions can be easily overwritten depending on the project's requirements.

The controller uses the ``Admin`` class to construct the different actions.
Inside the controller, the ``Admin`` object is accessible through the ``configuration``
property.

Obtaining an ``Admin`` Service
------------------------------

``Admin`` definition are accessible through the 'sonata_admin.pool' service or directly from the DIC.
The ``Admin`` definitions are lazy loaded from the DIC to avoid overhead.

Filter and Datagrid
-------------------

todo ...

.. _`Django Project Website`: http://www.djangoproject.com/