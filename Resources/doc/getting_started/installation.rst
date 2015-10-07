Installation
============

SonataAdminBundle is just a bundle and as such, you can install it at any
moment during a project's lifecycle.

1. Download the Bundle
----------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

.. code-block:: bash

    $ composer require sonata-project/admin-bundle "2.3.*"

This command requires you to have Composer installed globally, as explained in
the `installation chapter`_ of the Composer documentation.

1.1. Download a Storage Bundle
------------------------------

You've now downloaded the SonataAdminBundle. While this bundle contains all
functionality, it needs storage bundles to be able to communicate with a
database. Before using the SonataAdminBundle, you have to download one of these
storage bundles. The official storage bundles are:

* `SonataDoctrineORMAdminBundle`_ (integrates the Doctrine ORM);
* `SonataDoctrineMongoDBAdminBundle`_ (integrates the Doctrine MongoDB ODM);
* `SonataPropelAdminBundle`_ (integrates Propel);
* `SonataDoctrinePhpcrAdminBundle`_ (integrates the Doctrine PHPCR ODM).

You can download them in the same way as the SonataAdminBundle. For instance,
to download the SonataDoctrineORMAdminBundle, execute the following command:

.. code-block:: bash

    $ composer require sonata-project/doctrine-orm-admin-bundle "2.3.*"

.. tip::

    Don't know which to choose? Most new users prefer SonataDoctrineORMAdmin,
    to interact with traditional relational databases (MySQL, PostgreSQL, etc).

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle and the bundles is relies on by adding the following
line in the `app/AppKernel.php` file of your project:

.. code-block:: php

    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...

                // The admin requires some twig functions defined in the security
                // bundle, like is_granted. Register this bundle if it wasn't the case
                // already.
                new Symfony\Bundle\SecurityBundle\SecurityBundle(),

                // These are the other bundles the SonataAdminBundle relies on
                new Sonata\CoreBundle\SonataCoreBundle(),
                new Sonata\BlockBundle\SonataBlockBundle(),
                new Knp\Bundle\MenuBundle\KnpMenuBundle(),

                // And finally, the storage and SonataAdminBundle
                new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
                new Sonata\AdminBundle\SonataAdminBundle(),
            );

            // ...
        }

        // ...
    }

.. note::

    If a bundle is already registered somewhere in your ``AppKernel.php``, you
    should not register it again.

.. note::

    Since version 2.3, the bundle comes with jQuery and other front-end
    libraries. To update the versions (which isn't required), you can use
    `Bower`_. To make sure you get the dependencies that match the version of
    SonataAdminBundle you are using, you can make bower use the local bower
    dependency file, like this:

    .. code-block:: bash

        $ bower install ./vendor/sonata-project/admin-bundle/bower.json

Step 3: Configure the Installed Bundles
---------------------------------------

Now all needed bundles are downloaded and registered, you have to add some
configuration. The admin interface is using SonataBlockBundle to put everything
in blocks. You just have to tell the block bundle about the existence of the
admin block:

.. code-block:: yaml

    # app/config/config.yml
    sonata_block:
        default_contexts: [cms]
        blocks:
            # enable the SonataAdminBundle block
            sonata.admin.block.admin_list:
                contexts: [admin]
            # ...

.. note::

    Don't worry too much if, at this point, you don't yet understand fully
    what a block is. The SonataBlockBundle is a useful tool, but it's not vital
    that you understand it in order to use the admin bundle.

Step 4: Import Routing Configuration
------------------------------------

The bundles are now registered and configured correctly. Before you can use it,
the Symfony router needs to know the routes provided by the SonataAdminBundle.
You can do this by importing them in the routing configuration:

.. code-block:: yaml

    # app/config/routing.yml
    admin_area:
        resource: "@SonataAdminBundle/Resources/config/routing/sonata_admin.xml"
        prefix: /admin

Step 5: Preparing your Environment
----------------------------------

As with all bundles you install, it's a good practice to clear the cache and
install the assets:

.. code-block:: bash

    $ php app/console cache:clear
    $ php app/console assets:install

The Admin Interface
-------------------

You've finished the installation process, congratulations. If you fire up the
server, you can now visit the admin page on http://localhost:8000/admin

.. note::

    This tutorial assumes you are using the build-in server using the
    ``php app/console server:start`` (or ``server:run``) command.

.. image:: ../images/getting_started_empty_dashboard.png

As you can see, the admin panel is very empty. This is because no bundle has
provided admin functionality for the admin bundle yet. Fortunately, you'll
learn how to do this in the :doc:`next chapter <creating_an_admin>`.

.. _`installation chapter`: https://getcomposer.org/doc/00-intro.md
.. _SonataDoctrineORMAdminBundle: http://sonata-project.org/bundles/doctrine-orm-admin/master/doc/index.html
.. _SonataDoctrineMongoDBAdminBundle: http://sonata-project.org/bundles/mongo-admin/master/doc/index.html
.. _SonataPropelAdminBundle: http://sonata-project.org/bundles/propel-admin/master/doc/index.html
.. _SonataDoctrinePhpcrAdminBundle: http://sonata-project.org/bundles/doctrine-phpcr-admin/master/doc/index.html
.. _Bower: http://bower.io/
