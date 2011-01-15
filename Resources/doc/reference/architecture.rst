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

* ``listFields``: The fields displayed in the list table;
* ``filterFields``: The fields available for filter the list;
* ``formFields``: The fields used to edit the entity;
* ``formGroups``: The group definition where a field must be displayed (edit form)
* The batch actions: Actions that can be performed on a group of entities
  (e.g. bulk delete)

If a field is associated with another entity (and that entity also has an
``Admin`` class), then the related ``Admin`` class will be accessible from
within the first ``Admin`` class.

The admin class is ContainerAware, meaning that the entire dependency injection
container is injected. Therefore, you have access to every service and can
do things such as:

* Access user permissions to define the list fields;
* Access the ``Router`` to generate custom routes.

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

* ``BaseApplicationBundle::standard_layout.twig``
* ``BaseApplicationBundle::ajax_layout.twig``

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

``Admin`` definition are accessible through the 'base_application.pool' service.
The ``Admin`` definitions are lazy loaded from the Pool to avoid overhead.

Filter and Datagrid
-------------------

todo ...

.. _`Django Project Website`: http://www.djangoproject.com/