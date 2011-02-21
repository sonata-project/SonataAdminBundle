Installation
============

Make sure you have ``Sonata`` and ``Knplabs`` exists, if not create them::

  mkdir src/Sonata
  mkdir src/Knplabs

To begin, add the dependent bundles to the ``src/`` directory. If using
git, you can add them as submodules::

  git submodule add git@github.com:sonata-project/jQueryBundle.git src/Sonata/jQueryBundle
  git submodule add git@github.com:sonata-project/BluePrintBundle.git src/Sonata/BluePrintBundle
  git submodule add git@github.com:sonata-project/BaseApplicationBundle.git src/Sonata/BaseApplicationBundle
  git submodule add git@github.com:sonata-project/MenuBundle.git src/Knplabs/MenuBundle

Next, be sure to enable the bundles in your application kernel:

.. code-block:: php

  // app/AppKernel.php
  public function registerBundles()
  {
      return array(
          // ...
          new Sonata\jQueryBundle\SonatajQueryBundle(),
          new Sonata\BluePrintBundle\SonataBluePrintBundle(),
          new Sonata\BaseApplicationBundle\SonataBaseApplicationBundle(),
          new Knplabs\Knplabs\KnplabsMenuBundle(),
          // ...
      );
  }

Configuration
-------------

To use the ``BaseApplicationBundle``, add the following to your application
configuration file.

.. code-block:: yaml

    # app/config/config.yml
    sonata_base_application: ~

    menu.twig:   ~

The bundle also contains several routes. Import them by adding the following
code to your application's routing file:

- Add the BaseApplicationBundle's routing definition

.. code-block:: yaml

    # app/config/routing.yml
    base_application:
        resource: '@SonataBaseApplicationBundle/Resources/config/routing/base_application.xml'
        prefix: /admin

    admin:
        resource: '@SonataBaseApplicationBundle/Resources/config/routing/base_application.admin'
        prefix: /admin

At this point you can access to the dashboard with the url: ``http://yoursite.local/admin/dashboard``.

.. note::

    If you're using XML or PHP to specify your application's configuration,
    the above configuration and routing will actually be placed in those
    files, with the correct format (i.e. XML or PHP).


Declaring new Entity
--------------------

Once you have created an admin class, you must declare the class to use it. Like ::

.. code-block:: yaml

    # app/config/config.yml
    sonata_base_application:
        entities:
            post:
                label:      Post
                group:      blog
                class:      Sonata\NewsBundle\Admin\PostAdmin
                entity:     Application\Sonata\NewsBundle\Entity\Post
                controller: SonataNewsBundle:PostAdmin

            tag:
                label:      Tag
                group:      blog
                class:      Sonata\NewsBundle\Admin\TagAdmin
                entity:     Application\Sonata\NewsBundle\Entity\Tag
                controller: SonataNewsBundle:TagAdmin

            comment:
                label:      Comment
                group:      blog
                class:      Sonata\NewsBundle\Admin\CommentAdmin
                entity:     Application\Sonata\NewsBundle\Entity\Comment
                controller: SonataNewsBundle:CommentAdmin
