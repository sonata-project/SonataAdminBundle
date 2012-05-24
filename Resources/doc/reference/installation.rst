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

You will need to install those in their 2.0 branches (or master if they don't
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
Alter your deps file, and add these lines :

.. code-block:: ini

    [SonataAdminBundle]
        git=git://github.com/sonata-project/SonataAdminBundle.git
        target=/bundles/Sonata/AdminBundle
        version=origin/2.0

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

and finally run the vendors script to download bundles::

  php bin/vendors install

Next, be sure to enable this bundle in your autoload.php (if it is not already
in there) and AppKernel.php files:

.. code-block:: php

    <?php
    // app/autoload.php
    $loader->registerNamespaces(array(
        // ...
        'Sonata'     => __DIR__.'/../vendor/bundles',
        // ...
    ));

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Sonata\AdminBundle\SonataAdminBundle(),
            // ...
        );
    }

Now, install the assets from the bundles::

    php app/console assets:install web

Usually when installing new bundles a good practice is to also delete your cache::

    php app/console cache:clear

After you have successfully installed above bundles you need to configure
SonataAdminBundle for administering your models. All that is needed to quickly
set up SonataAdminBundle is described in the next chapter : :doc:`getting_started`.
