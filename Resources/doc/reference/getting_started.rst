Getting started with SonataAdminBundle
======================================

If you followed the installation instructions, SonataAdminBundle should be installed
but inaccessible. You first need to configure it for your models before you can
start using it. Here is a quick checklist of what is needed to quickly setup
SonataAdminBundle and create your first admin interface for the models of your application:

* Step 1: Define SonataAdminBundle routes
* Step 2: Create an Admin class
* Step 3: Create an Admin service
* Step 4: Configuration

Define SonataAdminBundle routes
-------------------------------

To be able to access SonataAdminBundle's pages, you need to add its routes
to your application's routing file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml

        admin:
            resource: '@SonataAdminBundle/Resources/config/routing/sonata_admin.xml'
            prefix: /admin

        _sonata_admin:
            resource: .
            type: sonata_admin
            prefix: /admin

.. note::

    If you're using XML or PHP to specify your application's configuration,
    the above routing configuration must be placed in routing.xml or
    routing.php according to your format (i.e. XML or PHP).

.. note::

    For those curious about the ``resource: .`` setting: it is unusual syntax but used
    because Symfony requires a resource to be defined (which points to a real file).
    Once this validation passes Sonata's ``AdminPoolLoader`` is in charge of processing
    this route and it simply ignores the resource setting.

At this point you can already access the (empty) admin dashboard by visiting the URL:
``http://yoursite.local/admin/dashboard``.


Create an Admin class
---------------------

SonataAdminBundle helps you manage your data using a graphic interface that
will let you create, update or search your model's instances. Those actions need to
be configured, which is done using an Admin class.

An Admin class represents the mapping of your model to each administration action.
In it, you decide which fields to show on a listing, which to use as filters or what
to show in a creation or edition form.

The easiest way to create an Admin class for your model is to extend
the ``Sonata\AdminBundle\Admin\AbstractAdmin`` class.

Suppose your ``AppBundle`` has a ``Post`` entity.
This is how a basic Admin class for it could look like:

.. code-block:: php

    <?php
    // src/AppBundle/Admin/PostAdmin.php

    namespace AppBundle\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Show\ShowMapper;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;

    class PostAdmin extends AbstractAdmin
    {
        // Fields to be shown on create/edit forms
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('title', 'text', array(
                    'label' => 'Post Title'
                ))
                ->add('author', 'entity', array(
                    'class' => 'AppBundle\Entity\User'
                ))

                // if no type is specified, SonataAdminBundle tries to guess it
                ->add('body')

                // ...
           ;
        }

        // Fields to be shown on filter forms
        protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        {
           $datagridMapper
                ->add('title')
                ->add('author')
           ;
        }

        // Fields to be shown on lists
        protected function configureListFields(ListMapper $listMapper)
        {
            $listMapper
                ->addIdentifier('title')
                ->add('slug')
                ->add('author')
           ;
        }

        // Fields to be shown on show action
        protected function configureShowFields(ShowMapper $showMapper)
        {
            $showMapper
               ->add('title')
               ->add('slug')
               ->add('author')
           ;
        }
    }

Implementing these four functions is the first step to creating an Admin class.
Other options are available, that will let you further customize the way your model
is shown and handled. Those will be covered in more advanced chapters of this manual.

Create an Admin service
-----------------------

Now that you have created your Admin class, you need to create a service for it. This
service needs to have the ``sonata.admin`` tag, which is your way of letting
SonataAdminBundle know that this particular service represents an Admin class:

Create either a new ``admin.xml`` or ``admin.yml`` file inside the ``src/AppBundle/Resources/config/`` folder:

.. configuration-block::

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/admin.xml -->

        <service id="app.admin.post" class="AppBundle\Admin\PostAdmin">
            <tag name="sonata.admin" manager_type="orm" group="Content" label="Post" />
            <argument />
            <argument>AppBundle\Entity\Post</argument>
            <argument />
            <call method="setTranslationDomain">
                <argument>AppBundle</argument>
            </call>
        </service>

    .. code-block:: yaml

        # src/AppBundle/Resources/config/admin.yml

        services:
            app.admin.post:
                class: AppBundle\Admin\PostAdmin
                tags:
                    - { name: sonata.admin, manager_type: orm, group: "Content", label: "Post" }
                arguments:
                    - ~
                    - AppBundle\Entity\Post
                    - ~
                calls:
                    - [ setTranslationDomain, [AppBundle]]

The example above assumes that you're using ``SonataDoctrineORMAdminBundle``.
If you're using ``SonataDoctrineMongoDBAdminBundle``, ``SonataPropelAdminBundle`` or ``SonataDoctrinePhpcrAdminBundle`` instead, set ``manager_type`` option to ``doctrine_mongodb``, ``propel`` or ``doctrine_phpcr`` respectively.

The basic configuration of an Admin service is quite simple. It creates a service
instance based on the class you specified before, and accepts three arguments:

    1. The Admin service's code (defaults to the service's name)
    2. The model which this Admin class maps (required)
    3. The controller that will handle the administration actions (defaults to ``SonataAdminBundle:CRUDController()``)

Usually you just need to specify the second argument, as the first and third's default
values will work for most scenarios.

The ``setTranslationDomain`` call lets you choose which translation domain to use when
translating labels on the admin pages. If you don't call ``setTranslationDomain``, SonataAdmin uses ``messages`` as translation domain.
More info on the `Symfony translations page`_.

Now that you have a configuration file with your admin service, you just need to tell
Symfony to load it. There are two ways to do so:

Have your bundle load it
^^^^^^^^^^^^^^^^^^^^^^^^

Inside your bundle's extension file, using the ``load()`` method as described in the `Symfony cookbook`_.

For ``admin.xml`` use:

.. code-block:: php

    <?php
    // src/AppBundle/DependencyInjection/AppExtension.php

    namespace AppBundle\DependencyInjection;

    use Symfony\Component\HttpKernel\DependencyInjection\Extension;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader;
    use Symfony\Component\Config\FileLocator;

    class AppExtension extends Extension
    {
        public function load(array $configs, ContainerBuilder $container) {
            // ...
            $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            // ...
            $loader->load('admin.xml');
        }
    }

and for ``admin.yml``:

.. code-block:: php

    <?php
    // src/AppBundle/DependencyInjection/AppExtension.php

    namespace AppBundle\DependencyInjection;

    use Symfony\Component\DependencyInjection\Loader;
    use Symfony\Component\Config\FileLocator;

    class AppExtension extends Extension
    {
        public function load(array $configs, ContainerBuilder $container)
        {
            // ...
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            // ...
            $loader->load('admin.yml');
        }
    }

Importing it in the main config.yml
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

We recommend the to load the file in the Extension, but this way is possible, too.

You can include your new configuration file in the main ``config.yml`` (make sure that you
use the correct file extension):

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        imports:

            # for xml
            - { resource: "@AppBundle/Resources/config/admin.xml" }

            # for yaml
            - { resource: "@AppBundle/Resources/config/admin.yml" }

Configuration
-------------

At this point you have basic administration actions for your model. If you visit ``http://yoursite.local/admin/dashboard`` again, you should now see a panel with
your mapped model. You can start creating, listing, editing and deleting instances.

You probably want to put your own project's name and logo on the top bar.

Put your logo file here ``src/AppBundle/Resources/public/images/fancy_acme_logo.png``

Install your assets:

.. code-block:: bash

    $ php app/console assets:install

Now you can change your project's main config.yml file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml

        sonata_admin:
            title:      Acme
            title_logo: bundles/app/images/fancy_acme_logo.png

Next steps - Security
---------------------

As you probably noticed, you were able to access your dashboard and data by just
typing in the URL. By default, the SonataAdminBundle does not come with any user
management for ultimate flexibility. However, it is most likely that your application
requires such a feature. The Sonata Project includes a ``SonataUserBundle`` which
integrates the very popular ``FOSUserBundle``. Please refer to the :doc:`security` section of
this documentation for more information.

Congratulations! You are ready to start using SonataAdminBundle. You can now map
additional models or explore advanced functionalities. The following sections will
each address a specific section or functionality of the bundle, giving deeper
details on what can be configured and achieved with SonataAdminBundle.

.. _`Symfony cookbook`: http://symfony.com/doc/master/cookbook/bundles/extension.html#using-the-load-method
.. _`Symfony translations page`: http://symfony.com/doc/current/book/translation.html#using-message-domains
