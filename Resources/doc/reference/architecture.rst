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
      
.. code-block:: yaml

    services:
        sonata.admin.tag:
            class: Acme\DemoBundle\Admin\PostAdmin
            tags:
                - { name: sonata.admin, manager_type: orm, group: "Content", label: "Post" }
            arguments:
                - ~
                - Acme\DemoBundle\Entity\Post
                - ~
            calls:
                - [ setLabelTranslatorStrategy, [sonata.admin.label.strategy.underscore]]

Here, we declare the same Admin service as before, but using a different label translator strategy, 
replacing the default one. Notice that ``sonata.admin.label.strategy.underscore`` is a 
service provided by SonataAdminBundle, but you could just as easily use a service of your
own.

CRUDController
--------------

The ``CRUDController`` contains the actions you have available to manipulate
your model instances, like list, create or delete. It uses the ``Admin`` class
to determine its behavior, like which fields to display in the edition form, or
how to build the list view. Inside the ``CRUDController``, you can find the
``Admin`` class instance in the ``$admin``.

The ``CRUDController`` is no different than any other Symfony2 controller,
meaning you have all the usual options available to you, like getting services
from the Dependency Injection Container (DIC).

This is particulary useful if you decide to extend the ``CRUDController``, to
add new actions or change the behavior of existing ones. You can specify which controller
to use when declaring the Admin service, by passing it as the 3rd argument:

.. code-block:: xml

    <services>
       <service id="sonata.admin.tag" class="Acme\DemoBundle\Admin\PostAdmin">
          <tag name="sonata.admin" manager_type="orm" group="Content" label="Post"/>
          <argument />
          <argument>Acme\DemoBundle\Entity\Post</argument>
          <argument>AcmeDemoBundle:PostAdmin</argument>
          <call method="setTranslationDomain">
              <argument>AcmeDemoBundle</argument>
          </call>
      </service>
   </services>
    
.. code-block:: yaml

    services:
        sonata.admin.tag:
            class: Acme\DemoBundle\Admin\PostAdmin
            tags:
                - { name: sonata.admin, manager_type: orm, group: "Content", label: "Post" }
            arguments:
                - ~
                - Acme\DemoBundle\Entity\Post
                - AcmeDemoBundle:PostAdmin
            calls:
                - [ setTranslationDomain, [AcmeDemoBundle]]

When extending a ``CRUDController``, remember that the ``Admin`` class already has
a set of automatically injected dependencies that are useful when implementing several
scenarios. Refer to the existing ``CRUDController`` actions for examples on how to get
the best out of them. 

Fields Definition
-----------------

Your ``Admin`` class will map your model's fields to a field in every action defined in you ``CRUDController``. So, for each action, a list of field mappings is generated. These lists 
are implemented using the ``FieldDescriptionCollection`` class which, stores instances of
``FieldDescriptionInterface``. Picking up on our previous ``Admin`` class example:

.. code-block:: php

   namespace Acme\DemoBundle\Admin;

   use Sonata\AdminBundle\Admin\Admin;
   use Sonata\AdminBundle\Datagrid\ListMapper;
   use Sonata\AdminBundle\Datagrid\DatagridMapper;
   use Sonata\AdminBundle\Form\FormMapper;

   class PostAdmin extends Admin
   {
       //Fields to be shown on create/edit forms
       protected function configureFormFields(FormMapper $formMapper)
       {
           $formMapper
               ->add('title', 'text', array('label' => 'Post Title'))
               ->add('author', 'entity', array('class' => 'Acme\DemoBundle\Entity\User'))
               ->add('body') //if no type is specified, SonataAdminBundle tries to guess it
           ;
       }

       //Fields to be shown on filter forms
       protected function configureDatagridFilters(DatagridMapper $datagridMapper)
       {
           $datagridMapper
               ->add('title')
               ->add('author')
           ;
       }

       //Fields to be shown on lists
       protected function configureListFields(ListMapper $listMapper)
       {
           $listMapper
               ->addIdentifier('title')
               ->add('slug')
               ->add('author')
           ;
       }
   }

Internally, the provided ``Admin`` class will use these three functions to create three 
``FieldDescriptionCollection`` instances: 

* ``$formFieldDescriptions``, containing three ``FieldDescriptionInterface`` instances
* ``$filterFieldDescriptions``, containing two ``FieldDescriptionInterface`` instances
* ``$listFieldDescriptions``, containing three ``FieldDescriptionInterface`` instances

The actual ``FieldDescription`` implementation is provided by the storage
abstraction bundle that you choose during the installation process, based on the
``BaseFieldDescription`` abstract class provided by ``SonataAdminBundle``.

Each ``FieldDescription`` contains various details about a field mapping. Some of
them are independent of the action in which they are used, like ``name`` or ``type``,
while other are used only in specific actions. More information can be found on the
``BaseFieldDescription`` class file.

In most scenarios, you won't actually need to handle ``FieldDescription`` yourself.
However, it is important that you know it exists and how it's used, as it sits at the
core of ``SonataAdminBundle``.

Templates
---------

Like all actions, ``CRUDController`` actions needs templates in which to be rendered.
``SonataAdminBundle`` provides ready to use views as well as ways to easily customize them.

The current implementation uses Twig as the template engine. All templates
are located in the ``Resources/views`` directory of the bundle.

There are two base templates, one of which is ultimately used in every action:

* ``SonataAdminBundle::standard_layout.html.twig``
* ``SonataAdminBundle::ajax_layout.html.twig``

Like the names say, one if for standard calls, the other one for AJAX.

The subfolders include Twig files for specific sections of ``SonataAdminBundle``:

* Block: ``SonataBlockBundle`` block views. Right now it only has one, that displays all the mapped classes on the dashboard
* Button: Buttons such as ``Add new`` or ``Delete`` that you can see across several CRUD actions
* CRUD: Base views for every CRUD action, plus several field views for each field type
* Core: Dashboard view, together with deprecated and stub twig files.
* Form: Views related to form rendering
* Helper: a view providing a short object description, as part of a specific form field type provided by ``SonataAdminBundle``
* Pager: Pagination related view files

These will be discussed in greater detail in the specific :doc:`templates` section, where 
you will also find instructions on how to configure ``SonataAdminBundle`` to use your templates
instead of the default ones.

Managing ``Admin`` Service
------------------------------

You ``Admin`` service definitions are parsed when Symfony2 is loaded, and handled by
the ``Pool`` class. This class, available as the ``sonata.admin.pool`` service from the
DIC (dependency injection container), handles the ``Admin`` classes lazy-loading from the DIC to reduce overhead.



.. _`Django Project Website`: http://www.djangoproject.com/
