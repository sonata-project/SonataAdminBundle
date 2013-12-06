Installation
============

SonataAdminBundle can be installed at any moment during a project's lifecycle,
whether it's a clean Symfony2 installation or an existing project.

Downloading the code
--------------------

Use composer to manage your dependencies and download SonataAdminBundle:

.. code-block:: bash

    php composer.phar require sonata-project/admin-bundle

You'll be asked to type in a version constraint. 'dev-master' will get you the latest
version, compatible with the latest Symfony2 version. Check `packagist <https://packagist.org/packages/sonata-project/admin-bundle>`_
for older versions:

.. code-block:: bash

    Please provide a version constraint for the sonata-project/admin-bundle requirement: dev-master

Selecting and downloading a storage bundle
------------------------------------------

SonataAdminBundle is storage agnostic, meaning it can work with several storage
mechanism. Depending on which you are using on your project, you'll need to install
one of the following bundles. In the respective links you'll find simple installation
instructions for each of them:

    - `SonataDoctrineORMAdminBundle <http://sonata-project.org/bundles/doctrine-orm-admin/master/doc/reference/installation.html>`_
    - `SonataDoctrineMongoDBAdminBundle <https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/blob/master/Resources/doc/reference/installation.rst>`_
    - `SonataPropelAdminBundle <http://sonata-project.org/bundles/propel-admin/master/doc/reference/installation.html>`_
    - `SonataDoctrinePhpcrAdminBundle <https://github.com/sonata-project/SonataDoctrinePhpcrAdminBundle/blob/master/Resources/doc/reference/installation.rst>`_

.. note::
    Don't know which to choose? Most new users prefer SonataDoctrineORMAdmin, to interact with traditional relational databases (MySQL, PostgreSQL, etc)

Enabling SonataAdminBundle and its dependencies
-----------------------------------------------

SonataAdminBundle relies on other bundles to implement some features.
Besides the storage layer mentioned on step 2, there are other bundles needed
for SonataAdminBundle to work:

    - `SonataBlockBundle <http://sonata-project.org/bundles/block/master/doc/reference/installation.html>`_
    - `SonatajQueryBundle <https://github.com/sonata-project/SonatajQueryBundle/blob/documentation/Resources/doc/reference/installation.rst>`_
    - `KnpMenuBundle <https://github.com/KnpLabs/KnpMenuBundle/blob/master/Resources/doc/index.md#installation>`_ (Version 1.1.*)

These bundles are automatically downloaded by composer as a dependency of SonataAdminBundle.
However, you have to enable them in your AppKernel.php, and configure them manually. Don't
forget to enable SonataAdminBundle too:

.. code-block:: php

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...

            // Add your dependencies
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\jQueryBundle\SonatajQueryBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            //...

            // If you haven't already, add the storage bundle
            // This example uses SonataDoctrineORMAdmin but
            // it works the same with the alternatives
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),

            // Then add SonataAdminBundle
            new Sonata\AdminBundle\SonataAdminBundle(),
            // ...
        );
    }

.. note::
    If a dependency is already enabled somewhere in your AppKernel.php,
    you don't need to enable it again.


Configuring SonataAdminBundle dependencies
------------------------------------------

You will need to configure SonataAdminBundle's dependencies. For each of the above
mentioned bundles, check their respective installation/configuration instructions
files to see what changes you have to make to your Symfony2 configuration.

SonataAdminBundle provides a SonataBlockBundle block that's used on the administration
dashboard. To be able to use it, make sure it's enabled on SonataBlockBundle's configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        sonata_block:
            default_contexts: [cms]
            blocks:
                # Enable the SonataAdminBundle block
                sonata.admin.block.admin_list:
                    contexts:   [admin]
                # Your other blocks

.. note::
    Don't worry too much if, at this point, you don't yet understand fully
    what a block is. SonataBlockBundle is a useful tool, but it's not vital
    that you understand right now.

Cleaning up
-----------

Now, install the assets from the bundles:

.. code-block:: bash

    php app/console assets:install web

Usually, when installing new bundles, it's good practice to also delete your cache:

.. code-block:: bash

    php app/console cache:clear

At this point, your Symfony2 installation should be fully functional, with no errors
showing up from SonataAdminBundle or its dependencies. SonataAdminBundle is installed
but not yet configured (more on that in the next section), so you won't be able to
use it yet.

If, at this point or during the installation, you come across any errors, don't panic:

    -  Read the error message carefully. Try to find out exactly which bundle is causing the error. Is it SonataAdminBundle or one of the dependencies?
    - Make sure you followed all the instructions correctly, for both SonataAdminBundle and it's dependencies
    - Odds are that someone already had the same problem, and it's documented somewhere. Check `Google <http://www.google.com>`_, `Sonata Users Group <https://groups.google.com/group/sonata-users>`_, `Symfony2 Users Group <https://groups.google.com/group/symfony2>`_ and `Symfony Forum <forum.symfony-project.org>`_ to see if you can find a solution.
    - Still no luck? Try checking the project's open issues on GitHub.

After you have successfully installed the above bundles you need to configure
SonataAdminBundle for administering your models. All that is needed to quickly
set up SonataAdminBundle is described in the :doc:`getting_started` chapter.
