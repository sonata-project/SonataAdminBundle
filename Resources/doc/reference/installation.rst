Installation
============

To begin, add the dependent bundles to the ``src/Bundle`` directory. If using
git, you can add them as submodules::

  git submodule add git@github.com:sonata-project/jQueryBundle.git src/Bundle/jQueryBundle
  git submodule add git@github.com:sonata-project/BaseApplicationBundle.git src/Bundle/BaseApplicationBundle

Next, be sure to enable the bundles in your application kernel:

.. code-block:: php

  // app/AppKernel.php
  public function registerBundles()
  {
      return array(
          // ...
          new Bundle\jQueryBundle\jQueryBundle(),
          new Bundle\BluePrintBundle\BluePrintBundle(),
          new Bundle\BaseApplicationBundle\BaseApplicationBundle(),
          // ...
      );
  }

Configuration
-------------

To use the ``BaseApplicationBundle``, add the following to your application
configuration file.

.. code-block:: yaml

    # app/config/config.yml
    base_application.config: ~

The bundle also contains several routes. Import them by adding the following
code to your application's routing file:

- Add the BaseApplicationBundle's routing definition

.. code-block:: yaml

    # app/config/routing.yml
    base_application:
        resource: BaseApplicationBundle/Resources/config/routing/base_application.xml
        prefix: /admin

At this point you can access to the dashboard with the url: ``http://yoursite.local/admin/dashboard``.

.. note::

    If you're using XML or PHP to specify your application's configuration,
    the above configuration and routing will actually be placed in those
    files, with the correct format (i.e. XML or PHP).