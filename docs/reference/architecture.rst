Architecture
============

The architecture of the ``SonataAdminBundle`` is primarily inspired by
the Django Admin Project, which is truly a great project. More information
can be found at the `Django Project Website`_.

If you followed the instructions on the :doc:`../getting_started/creating_an_admin`
page, you should by now have an ``Admin`` class and an ``Admin`` service.
In this chapter, we'll discuss more in depth how it works.

The Admin Class
---------------

The ``Admin`` class maps a specific model to the rich CRUD interface provided by
``SonataAdminBundle``. In other words, using your ``Admin`` classes, you can configure
what is shown by ``SonataAdminBundle`` in each CRUD action for the associated model.
By now you've seen 3 of those actions in the :doc:`../getting_started/creating_an_admin` page: list,
filter and form (for creation/editing). However, a fully configured ``Admin`` class
can define more actions:

=============       =========================================================================
Actions             Description
=============       =========================================================================
list                The fields displayed in the list table
filter              The fields available for filtering the list
form                The fields used to create/edit the entity
show                The fields used to show the entity
batch actions       Actions that can be performed on a group of entities (e.g. bulk delete)
=============       =========================================================================

The ``Sonata\AdminBundle\Admin\AbstractAdmin`` class is provided to map your models, by extending it.
However, any implementation of the ``Sonata\AdminBundle\Admin\AdminInterface`` can be used to define
an ``Admin`` service. For each ``Admin`` service, the following required dependencies are automatically
injected by the bundle:

=========================       =========================================================================
Class                           Description
=========================       =========================================================================
ConfigurationPool               configuration pool where all Admin class instances are stored
ModelManager                    service which handles specific code relating to your persistence layer (e.g. Doctrine ORM)
FormContractor                  builds the forms for the edit/create views using the Symfony ``FormBuilder``
ShowBuilder                     builds the show fields
ListBuilder                     builds the list fields
DatagridBuilder                 builds the filter fields
Request                         the received http request
RouteBuilder                    allows you to add routes for new actions and remove routes for default actions
RouterGenerator                 generates the different URLs
SecurityHandler                 handles permissions for model instances and actions
Validator                       handles model validation
Translator                      generates translations
LabelTranslatorStrategy         a strategy to use when generating labels
MenuFactory                     generates the side menu, depending on the current action
=========================       =========================================================================

.. note::

    Each of these dependencies is used for a specific task, briefly described above.
    If you wish to learn more about how they are used, check the respective documentation
    chapter. In most cases, you won't need to worry about their underlying implementation.

All of these dependencies have default values that you can override when declaring any of
your ``Admin`` services. This is done using a ``call`` to the matching ``setter``:

.. configuration-block::

    .. code-block:: yaml

        services:
            app.admin.post:
                class: App\Admin\PostAdmin
                arguments:
                    - ~
                    - App\Entity\Post
                    - ~
                calls:
                    - [setLabelTranslatorStrategy, ['@sonata.admin.label.strategy.underscore']]
                tags:
                    - { name: sonata.admin, manager_type: orm, group: 'Content', label: 'Post' }

    .. code-block:: xml

        <service id="app.admin.post" class="App\Admin\PostAdmin">
              <argument/>
              <argument>App\Entity\Post</argument>
              <argument/>
              <call method="setLabelTranslatorStrategy">
                  <argument type="service" id="sonata.admin.label.strategy.underscore"/>
              </call>
              <tag name="sonata.admin" manager_type="orm" group="Content" label="Post"/>
          </service>

Here, we declare the same ``Admin`` service as in the :doc:`../getting_started/creating_an_admin`
chapter, but using a different label translator strategy, replacing the default one. Notice that
``sonata.admin.label.strategy.underscore`` is a service provided by ``SonataAdminBundle``,
but you could use a service of your own.

CRUDController
--------------

The ``CRUDController`` contains the actions you have available to manipulate
your model instances, like create, list, edit or delete. It uses the ``Admin``
class to determine its behavior, like which fields to display in the edit form,
or how to build the list view. Inside the ``CRUDController``, you can access the
``Admin`` class instance via the ``$admin`` variable.

.. note::

    `CRUD`_ is an acronym for "Create, Read, Update and Delete"

The ``CRUDController`` is no different from any other Symfony controller, meaning
that you have all the usual options available to you, like getting services from
the Dependency Injection Container (DIC).

This is particularly useful if you decide to extend the ``CRUDController`` to
add new actions or change the behavior of existing ones. You can specify which controller
to use when declaring the ``Admin`` service by passing it as the 3rd argument. For example
to set the controller to ``App\Controller\PostAdminController``:

.. configuration-block::

    .. code-block:: yaml

        services:
            app.admin.post:
                class: App\Admin\PostAdmin
                arguments:
                    - ~
                    - App\Entity\Post
                    - App\Controller\PostAdminController
                calls:
                    - [setTranslationDomain, ['App']]
                tags:
                    - { name: sonata.admin, manager_type: orm, group: 'Content', label: 'Post' }

    .. code-block:: xml

        <service id="app.admin.post" class="App\Admin\PostAdmin">
            <argument/>
            <argument>App\Entity\Post</argument>
            <argument>App\Controller\PostAdminController</argument>
            <call method="setTranslationDomain">
                <argument>App</argument>
            </call>
            <tag name="sonata.admin" manager_type="orm" group="Content" label="Post"/>
        </service>

When extending ``CRUDController``, remember that the ``Admin`` class already has
a set of automatically injected dependencies that are useful when implementing several
scenarios. Refer to the existing ``CRUDController`` actions for examples of how to get
the best out of them.

In your overloaded CRUDController you can overload also these methods to limit
the number of duplicated code from SonataAdmin:
* ``preCreate``: called from ``createAction``
* ``preEdit``: called from ``editAction``
* ``preDelete``: called from ``deleteAction``
* ``preShow``: called from ``showAction``
* ``preList``: called from ``listAction``

These methods are called after checking the access rights and after retrieving the object
from database. You can use them if you need to redirect user to some other page under certain conditions.

Fields Definition
-----------------

Your ``Admin`` class defines which of your model's fields will be available in each
action defined in your ``CRUDController``. So, for each action, a list of field mappings
is generated. These lists are implemented using the ``FieldDescriptionCollection`` class
which stores instances of ``FieldDescriptionInterface``. Picking up on our previous
``PostAdmin`` class example::

    // src/Admin/PostAdmin.php

    namespace App\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Show\ShowMapper;
    use Symfony\Bridge\Doctrine\Form\Type\EntityType;
    use Symfony\Component\Form\Extension\Core\Type\TextType;
    use App\Entity\User;

    final class PostAdmin extends AbstractAdmin
    {
        // Fields to be shown on create/edit forms
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('title', TextType:class, [
                    'label' => 'Post Title'
                ])
                ->add('author', EntityType::class, [
                    'class' => User::class
                ])

                // "privateNotes" field will be rendered only if the authenticated
                // user is granted with the "ROLE_ADMIN_MODERATOR" role
                ->add('privateNotes', null, [], [
                    'role' => 'ROLE_ADMIN_MODERATOR'
                ])

                // if no type is specified, SonataAdminBundle tries to guess it
                ->add('body')

                // conditionally add "status" field if the subject already exists
                // `ifFalse()` is also available to build this kind of condition
                ->ifTrue($this->hasSubject())
                    ->add('status')
                ->ifEnd()

                // ...
            ;
        }

        // Fields to be shown on filter forms
        protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        {
            $datagridMapper
                ->add('title')
                ->add('author')
                ->add('privateNotes', null, [], null, null, [
                    'role' => 'ROLE_ADMIN_MODERATOR'
                ])
            ;
        }

        // Fields to be shown on lists
        protected function configureListFields(ListMapper $listMapper)
        {
            $listMapper
                ->addIdentifier('title')
                ->add('slug')
                ->add('author')
                ->add('privateNotes', null, [
                    'role' => 'ROLE_ADMIN_MODERATOR'
                ])
            ;
        }

        // Fields to be shown on show action
        protected function configureShowFields(ShowMapper $showMapper)
        {
            $showMapper
                ->add('id')
                ->add('title')
                ->add('slug')
                ->add('author')
                ->add('privateNotes', null, [
                    'role' => 'ROLE_ADMIN_MODERATOR'
                ])
            ;
        }
    }

Internally, the provided ``Admin`` class will use these three functions to create three
``FieldDescriptionCollection`` instances:

* ``$formFieldDescriptions``, containing four (and conditionally five) ``FieldDescriptionInterface``
  instances for title, author, body and privateNotes (and status, if the condition is met)
* ``$filterFieldDescriptions``, containing three ``FieldDescriptionInterface`` instances
  for title, author and privateNotes
* ``$listFieldDescriptions``, containing four ``FieldDescriptionInterface`` instances
  for title, slug, author and privateNotes
* ``$showFieldDescriptions``, containing five ``FieldDescriptionInterface`` instances
  for id, title, slug, author and privateNotes

The actual ``FieldDescription`` implementation is provided by the storage abstraction
bundle that you choose during the installation process, based on the
``BaseFieldDescription`` abstract class provided by ``SonataAdminBundle``.

Each ``FieldDescription`` contains various details about a field mapping. Some of
them are independent of the action in which they are used, like ``name`` or ``type``,
while others are used only in specific actions. More information can be found in the
``BaseFieldDescription`` class file.

In most scenarios, you will not actually need to handle the ``FieldDescription`` yourself.
However, it is important that you know it exists and how it is used, as it sits at the
core of ``SonataAdminBundle``.

Templates
---------

Like most actions, ``CRUDController`` actions use view files to render their output.
``SonataAdminBundle`` provides ready to use views as well as ways to customize them.

The current implementation uses ``Twig`` as the template engine. All templates
are located in the ``Resources/views`` directory of the bundle.

There are two base templates, one of these is ultimately used in every action:

* ``@SonataAdmin/standard_layout.html.twig``
* ``@SonataAdmin/ajax_layout.html.twig``

Like the names say, one if for standard calls, the other one for AJAX.

The subfolders include Twig files for specific sections of ``SonataAdminBundle``:

Block:
  ``SonataBlockBundle`` block views. By default there is only one, which
  displays all the mapped classes on the dashboard
Button:
  Buttons such as ``Add new`` or ``Delete`` that you can see across several
  CRUD actions
CRUD:
  Base views for every CRUD action, plus several field views for each field type
Form:
  Views related to form rendering
Helper:
  A view providing a short object description, as part of a specific form field
  type provided by ``SonataAdminBundle``
Pager:
  Pagination related view files

These will be discussed in greater detail in the specific :doc:`templates` section, where
you will also find instructions on how to configure ``SonataAdminBundle`` to use your templates
instead of the default ones.

Managing ``Admin`` Service
--------------------------

Your ``Admin`` service definitions are parsed when Symfony is loaded, and handled by
the ``Pool`` class. This class, available as the ``sonata.admin.pool`` service from the
DIC, handles the ``Admin`` classes, lazy-loading them on demand (to reduce overhead)
and matching each of them to a group. It is also responsible for handling the top level
template files, administration panel title and logo.

.. _`Django Project Website`: https://www.djangoproject.com/
.. _`CRUD`: https://en.wikipedia.org/wiki/CRUD
