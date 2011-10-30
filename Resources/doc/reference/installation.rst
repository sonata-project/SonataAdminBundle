Installation
============

Download bundles
----------------

To begin, add the dependent bundles to the ``vendor/bundles`` directory. Add
the following lines to the file ``deps``::

  [SonatajQueryBundle]
      git=http://github.com/sonata-project/SonatajQueryBundle.git
      target=/bundles/Sonata/jQueryBundle

  [SonataUserBundle]
      git=http://github.com/sonata-project/SonataUserBundle.git
      target=/bundles/Sonata/UserBundle

  [SonataAdminBundle]
      git=http://github.com/sonata-project/SonataAdminBundle.git
      target=/bundles/Sonata/AdminBundle

  [MenuBundle]
      git=http://github.com/knplabs/KnpMenuBundle.git
      target=/bundles/Knp/Bundle/MenuBundle

  [KnpMenu]
      git=https://github.com/knplabs/KnpMenu.git
      target=/knp/menu

and run::

  bin/vendors install

Configuration
-------------

Next, be sure to enable the bundles in your autoload.php and AppKernel.php
files:

.. code-block:: php

  <?php
  // app/autoload.php
  $loader->registerNamespaces(array(
      // ...
      'Sonata'                         => __DIR__.'/../vendor/bundles',
      'Knp'                             => array(
          __DIR__.'/../vendor/bundles',
          __DIR__.'/../vendor/knp/menu/src',
      ),
      // ...
  ));

  // app/AppKernel.php
  public function registerBundles()
  {
      return array(
          // ...
          new Sonata\jQueryBundle\SonatajQueryBundle(),
          new Sonata\AdminBundle\SonataAdminBundle(),
          new Knp\Bundle\MenuBundle\KnpMenuBundle(),
          // ...
      );
  }

The bundle also contains several routes. Import them by adding the following
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

Now, install the assets from the different bundles:
``php app/console assets:install web --symlink``.
At this point you can access to the dashboard with the url:
``http://yoursite.local/admin/dashboard``.

.. note::

    If you're using XML or PHP to specify your application's configuration,
    the above configuration and routing will actually be placed in those
    files, with the correct format (i.e. XML or PHP).

The last important step is security, please refer to the dedicated section.