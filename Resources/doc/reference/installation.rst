Installation
============

Prerequisites
-------------

**Translations.**
If you wish to use default translation texts provided in this bundle, you have
to make sure you have translator enabled in your config.

.. code-block:: yaml

    # app/config/config.yml
    framework:
        translator: ~

Installation
------------

Download SonataAdminBundle and its dependencies to the ``vendor`` directory. You
can use Composer for the automated procces.

  php composer.phar require sonata-project/admin-bundle
  php composer.phar install

Next, be sure to enable this bundles in your AppKernel.php file:

.. code-block:: php

    <?php

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

If you didn't use Composer, you also need to add the bundle namespaces to your
autoload.php:

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

Now, install the assets from the bundles:

    php app/console assets:install web

Usually when installing new bundles a good practice is also to delete your cache:

    php app/console cache:clear

After you have successfully installed above bundles you need to configure
SonataAdminBundle for administering your models. All that is needed to quickly
set up SonataAdminBundle is described in the next chapter the Getting started
with SonataAdminBundle.
