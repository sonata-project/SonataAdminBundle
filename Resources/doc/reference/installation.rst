Installation
============

Prerequisites
-------------
PHP 5.3 and Symfony 2 are needed to make this bundle work ; there are also some
Sonata dependencies that need to be installed and configured beforehand :

    - `SonataCacheBundle <http://sonata-project.org/bundles/cache>`_
    - `SonataBlockBundle <http://sonata-project.org/bundles/block>`_
    - `SonatajQueryBundle <https://github.com/sonata-project/SonatajQueryBundle>`_
    - `KnpMenuBundle <https://github.com/KnpLabs/KnpMenuBundle/blob/master/Resources/doc/index.md#installation>`_ (Version 1.1.*)
    - `Exporter <https://github.com/sonata-project/exporter>`_

You will need to install those in their 2.1 branches (or master if they don't
have a similar branch). Follow also their configuration step ; you will find
everything you need in their installation chapter.

.. note::
    If a dependency is already installed somewhere in your project or in
    another dependency, you won't need to install it again.

Translations
~~~~~~~~~~~~
If you wish to use default translation texts provided in this bundle, you have
to make sure you have translator enabled in your config.

.. code-block:: yaml

    # app/config/config.yml
    framework:
        translator: ~

Installation
------------

Download SonataAdminBundle and its dependencies to the ``vendor`` directory. You
can use Composer for the automated process::

    php composer.phar require sonata-project/admin-bundle --no-update
    php composer.phar require sonata-project/doctrine-orm-admin-bundle --no-update  # optional
    php composer.phar require sonata-project/intl-bundle --no-update                # optional
    php composer.phar require sonata-project/cache-bundle --no-update               # optional
    php composer.phar update

Next, be sure to enable this bundles in your AppKernel.php file:

.. code-block:: php

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\CacheBundle\SonataCacheBundle(),
            new Sonata\jQueryBundle\SonatajQueryBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            // ...
        );
    }

You will also need to alter your ``app/config/config.yml`` file :

.. code-block:: yaml

    # app/config/config.yml
    sonata_block:
        default_contexts: [cms]
        blocks:
            sonata.admin.block.admin_list:
                contexts:   [admin]

            sonata.block.service.text:
            sonata.block.service.action:
            sonata.block.service.rss:


Now, install the assets from the bundles:

    php app/console assets:install web

Usually when installing new bundles a good practice is to also delete your cache::

    php app/console cache:clear

After you have successfully installed above bundles you need to configure
SonataAdminBundle for administering your models. All that is needed to quickly
set up SonataAdminBundle is described in the next chapter : :doc:`getting_started`.
