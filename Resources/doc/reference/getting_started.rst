Getting started with SonataAdminBundle
======================================

After installation of SonataAdminBundle you need to configure it for your models.
Here is a quick checklist of what is needed to quickly setup SonataAdminBundle
and create your first admin interface for the models of your application:

* Step 1: Define SonataAdminBundle routes
* Step 2: Setup the persistency service (ORM, ODM, ...)
* Step 3: Create admin class
* Step 4: Create admin service
* Step 5: Configuration
* Step 6: Security

Step 1: Define SonataAdminBundle routes
---------------------------------------

SonataAdminBundle contains several routes. Import them by adding the following
code to your application's routing file:

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

At this point you can already access the admin dashboard by visiting the url:
``http://yoursite.local/admin/dashboard``.


Step 2: Setup the persistence service (ORM, ODM, ...)
-----------------------------------------------------

SonataAdminBundle does not impose any persistency services (service for handling and
controlling your models), however most likely your application will use some
persistency services (like ORM or ODM for database and document stores) therefore
you can use the following bundles officially supported by Sonata Project's admin
bundle:

* SonataDoctrineORMAdminBundle
* SonataDoctrineMongoDBAdminBundle
* SonataDoctrinePhpcrAdminBundle

Propel users are warmly welcome to contribute and create a new bundle for Propel
ORM that will be integrated in SonataAdminBundle.

Install a persistency service you need and configure it according to their
related documentation.

Step 3: Create Admin class
--------------------------

Admin class represents mapping of your model and administration sections (forms,
list, show). The easiest way to create an admin class for your model is to extend
the ``Sonata\AdminBundle\Admin\Admin`` class. For filter, list and show views, you can
target a sub model property thanks to the dot-separated notation
(eg: ``mySubModel.mySubSubModel.myProperty``).

Here is a simple example from the SonataNewsBundle:

.. code-block:: php

   namespace Sonata\NewsBundle\Admin;

   use Sonata\AdminBundle\Admin\Admin;
   use Sonata\AdminBundle\Datagrid\ListMapper;
   use Sonata\AdminBundle\Datagrid\DatagridMapper;
   use Sonata\AdminBundle\Form\FormMapper;

   class TagAdmin extends Admin
   {
       protected function configureFormFields(FormMapper $formMapper)
       {
           $formMapper
               ->add('name')
               ->add('enabled', null, array('required' => false))
           ;
       }

       protected function configureDatagridFilters(DatagridMapper $datagridMapper)
       {
           $datagridMapper
               ->add('name')
               ->add('posts')
           ;
       }

       protected function configureListFields(ListMapper $listMapper)
       {
           $listMapper
               ->addIdentifier('name')
               ->add('slug')
               ->add('enabled')
           ;
       }
   }


Step 4: Create admin service
----------------------------

To notify your administration of your new admin class you need to create an
admin service and link it into the framework by setting the sonata.admin tag.

Create either a new ``admin.xml`` or ``admin.yml`` file inside the ``MyBundle/Resources/config/`` folder:

.. code-block:: xml

   <!-- MyBundle/Resources/config/admin.xml -->
   <container xmlns="http://symfony.com/schema/dic/services"
       xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
       xsi:schemaLocation="http://symfony.com/schema/dic/services/services-1.0.xsd">
       <services>
          <service id="sonata.admin.tag" class="YourNS\AdminBundle\Admin\BlogAdmin">
             <tag name="sonata.admin" manager_type="orm" group="Posts" label="Blog"/>
             <argument />
             <argument>YourNS\AdminBundle\Entity\Course</argument>
             <argument>SonataAdminBundle:CRUD</argument>
             <call method="setTranslationDomain">
                 <argument>YourNSAdminBundle</argument>
             </call>
         </service>
      </services>
   </container>


.. code-block:: yaml

   # MyBundle/Resources/config/admin.yml
   services:
       sonata.admin.tag:
           class: YourNS\AdminBundle\Admin\BlogAdmin
           tags:
               - { name: sonata.admin, manager_type: orm, group: posts, label: "Blog" }
           arguments:
               - ~
               - YourNS\AdminBundle\Entity\Course
               - 'SonataAdminBundle:CRUD'
           calls:
               - [ setTranslationDomain, [YourNSAdminBundle]]

Now include your new configuration file in the framework (make sure that your ``resource`` value has the
correct file extension depending on the code block that you used above):

.. code-block:: yaml

    # app/config/config.yml
    imports:
        - { resource: @MyBundle/Resources/config/admin.xml }

Or you can load the file inside with the Bundle's extension file using the ``load()`` method as described
in the `symfony cookbook`_.

.. code-block:: php
    
    # YourNS/AdminBundle/DependencyInjection/YourNSAdminBundleExtension.php for XML configurations

    use Symfony\Component\DependencyInjection\Loader;
    use Symfony\Component\Config\FileLocator;

    public function load(array $configs, ContainerBuilder $container) {
        // ... 
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('admin.xml');
    }

.. code-block:: php
    
    # YourNS/AdminBundle/DependencyInjection/YourNSAdminBundleExtension.php for YAML configurations

    use Symfony\Component\DependencyInjection\Loader;
    use Symfony\Component\Config\FileLocator;

    public function load(array $configs, ContainerBuilder $container) {
        // ... 
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('admin.yml');
    }

Step 5: Configuration
---------------------

At this point you have basic administration for your model. If you wish to
quickly customize your administration you can create some configuration options
and change them according to your requirements:

.. code-block:: yaml

    # app/config/config.yml
    sonata_admin:
        title:      Sonata Project
        title_logo: /bundles/sonataadmin/logo_title.png
        templates:
            # default global templates
            layout:  SonataAdminBundle::standard_layout.html.twig
            ajax:    SonataAdminBundle::ajax_layout.html.twig

            # default actions templates, should extend a global templates
            list:    SonataAdminBundle:CRUD:list.html.twig
            show:    SonataAdminBundle:CRUD:show.html.twig
            edit:    SonataAdminBundle:CRUD:edit.html.twig

        dashboard:
            blocks:
                # display a dashboard block
                - { position: left, type: sonata.admin.block.admin_list }

Linking the admin class to the dashboard is done automatically because of the
default option you defined above:

.. code-block:: yaml

    dashboard
        blocks:
            # display a dashboard block
            - { position: left, type: sonata.admin.block.admin_list }


However you can define only admin groups you want to show in the dashboard by:

.. code-block:: yaml

    dashboard
        blocks:
            # display a dashboard block
            - { position: left, type: sonata.admin.block.admin_list }

        groups:
            sonata_page:
                label: Page
                items: ~

More information can be found in the configuration chapter of this documentation.


Step 6: Security
----------------

The last important step is security. By default, the SonataAdminBundle does not
come with any user management for ultimate flexibility, however it is most
likely your application requires such feature. The Sonata Project includes a
``SonataUserBundle`` which integrates the very popular ``FOSUserBundle``. Please
refer to the security section of this documentation for more information.


That should be it! Read next sections fore more verbose documentation of the
SonataAdminBundle and how to tweak it for your requirements.

.. _`symfony cookbook`: http://symfony.com/doc/master/cookbook/bundles/extension.html#using-the-load-method
