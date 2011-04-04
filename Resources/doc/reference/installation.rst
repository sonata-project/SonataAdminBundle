Installation
============

Make sure you have ``Sonata`` and ``Knplabs`` exists, if not create them::

  mkdir src/Sonata
  mkdir src/Knplabs

To begin, add the dependent bundles to the ``src/`` directory. If using
git, you can add them as submodules::

  git submodule add git@github.com:sonata-project/jQueryBundle.git src/Sonata/jQueryBundle
  git submodule add git@github.com:sonata-project/BluePrintBundle.git src/Sonata/BluePrintBundle
  git submodule add git@github.com:sonata-project/AdminBundle.git src/Sonata/AdminBundle
  git submodule add git@github.com:sonata-project/MenuBundle.git src/Knplabs/MenuBundle

Next, be sure to enable the bundles in your application kernel:

.. code-block:: php

  // app/autoload.php
  $loader->registerNamespaces(array(
      // ...
      'Sonata'                         => __DIR__.'/../src',
      'Knplabs'                        => __DIR__.'/../src',
      // ...
  ));

  // app/AppKernel.php
  public function registerBundles()
  {
      return array(
          // ...
          new Sonata\jQueryBundle\SonatajQueryBundle(),
          new Sonata\BluePrintBundle\SonataBluePrintBundle(),
          new Sonata\AdminBundle\SonataAdminBundle(),
          new Knplabs\MenuBundle\KnplabsMenuBundle(),
          // ...
      );
  }

Configuration
-------------

The bundle also contains several routes. Import them by adding the following
code to your application's routing file:

- Add the AdminBundle's routing definition

.. code-block:: yaml

    # app/config/routing.yml
    admin:
        resource: '@SonataAdmin/Resources/config/routing/sonata_admin.xml'
        prefix: /admin

    _sonata_admin:
        resource: .
        type: sonata_admin
        prefix: /admin

At this point you can access to the dashboard with the url: ``http://yoursite.local/admin/dashboard``.

.. note::

    If you're using XML or PHP to specify your application's configuration,
    the above configuration and routing will actually be placed in those
    files, with the correct format (i.e. XML or PHP).


Declaring new Entity
--------------------

Once you have created an admin class, you must declare the class to use it. Like ::

.. code-block:: xml

    # app/config/config.xml

    <service id="sonata.news.admin.post" class="Sonata\NewsBundle\Admin\PostAdmin">

        <tag name="sonata.admin" manager_type="orm" group="sonata_blog" label="post"/>

        <argument>Sonata\NewsBundle\Entity\Post</argument>
        <argument>SonataNewsBundle:PostAdmin</argument>
    </service>
