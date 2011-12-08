Installation
============

Download bundles
----------------

To begin, add the dependent bundles to the ``vendor/bundles`` directory. Add
the following lines to the file ``deps``::

  [SonatajQueryBundle]
      git=http://github.com/sonata-project/SonatajQueryBundle.git
      target=/bundles/Sonata/jQueryBundle

  [SonataAdminBundle]
      git=http://github.com/sonata-project/SonataAdminBundle.git
      target=/bundles/Sonata/AdminBundle

  [KnpMenuBundle]
      git=https://github.com/KnpLabs/KnpMenuBundle.git
      target=/bundles/Knp/Bundle/MenuBundle

  [KnpMenu]
      git=https://github.com/KnpLabs/KnpMenu.git
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
      'Sonata'     => __DIR__.'/../vendor/bundles',
      'Knp\Bundle' => __DIR__.'/../vendor/bundles',
      'Knp\Menu'   => __DIR__.'/../vendor/knp/menu/src',
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

Users management
----------------

By default, the AdminBundle does not come with any user management, however it is most likely the application
requires such feature. The Sonata Project includes a ``SonataUserBundle`` which integrates the ``FOSUserBundle``.

The ``FOSUserBundle`` adds support for a database-backed user system in Symfony2. It provides a flexible framework
for user management that aims to handle common tasks such as user login, registration and password retrieval.

The ``SonataUserBundle`` is just a thin wrapper to include the ``FOSUserBundle`` into the ``AdminBundle``. The
``SonataUserBundle`` includes :

* A default login area
* A default ``user_block`` template which is used to display the current user and the logout link
* 2 Admin class : User and Group
* A default class for User and Group.

There is a little magic in the ``SonataAdminBundle`` if the bundle detects the ``SonataUserBundle`` class, then
the default ``user_block`` template will be changed to use the one provided by the ``SonataUserBundle``.

The install process is available on the dedicated `SonataUserBundle's documentation area <http://sonata-project.org/bundles/user/master/doc/reference/installation.html>`_

