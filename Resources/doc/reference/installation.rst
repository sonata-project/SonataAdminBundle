Installation
============

Prerequisites
-------------

**Translations**
If you wish to use default translation texts provided in this bundle, you have
to make sure you have translator enabled in your config.

.. code-block:: yaml

    # app/config/config.yml
    framework:
        translator: ~


Installation
------------

Download SonataAdminBundle and its dependencies to the ``vendor`` directory. You
can use the Symfony's vendor script for the automated procces. Add the following
in your ``deps`` file::

  [SonataAdminBundle]
      git=http://github.com/sonata-project/SonataAdminBundle.git
      target=/bundles/Sonata/AdminBundle

  [SonataBlockBundle]
      git=http://github.com/sonata-project/SonataBlockBundle.git
      target=/bundles/Sonata/BlockBundle

  [SonataCacheBundle]
      git=http://github.com/sonata-project/SonataCacheBundle.git
      target=/bundles/Sonata/CacheBundle

  [SonatajQueryBundle]
      git=http://github.com/sonata-project/SonatajQueryBundle.git
      target=/bundles/Sonata/jQueryBundle

  [KnpMenuBundle]
      git=http://github.com/KnpLabs/KnpMenuBundle.git
      target=/bundles/Knp/Bundle/MenuBundle

  [KnpMenu]
      git=http://github.com/KnpLabs/KnpMenu.git
      target=/knp/menu

  [Exporter]
      git=http://github.com/sonata-project/exporter.git
      target=/exporter

and run the vendors script to download bundles::

  php bin/vendors install

Next, be sure to enable this bundles in your autoload.php and AppKernel.php
files:

.. code-block:: php

    <?php
    // app/autoload.php
    $loader->registerNamespaces(array(
        // ...
        'Sonata'     => __DIR__.'/../vendor/bundles',
        'Exporter'   => __DIR__.'/../vendor/exporter/lib',
        'Knp\Bundle' => __DIR__.'/../vendor/bundles',
        'Knp\Menu'   => __DIR__.'/../vendor/knp/menu/src',
        // ...
    ));

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\CacheBundle\SonataCacheBundle(),
            new Sonata\jQueryBundle\SonatajQueryBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            // ...
        );
    }

Now, install the assets from the bundles:
``php app/console assets:install web``.

Usually when installing new bundles a good practice is also to delete your cache:
``php app/console cache:clear``.


After you have successfully installed above bundles you need to configure
SonataAdminBundle for administering your models. All that is needed to quickly
set up SonataAdminBundle is described in the next chapter the Getting started
with SonataAdminBundle.