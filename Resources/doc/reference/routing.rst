Routing
=======

The default routes used in the CRUD controller are accessible through the
``Admin`` class.

The ``Admin`` class contains two routing methods:

* ``getRoutes()``: Returns the available routes;
* ``generateUrl($name, $options)``: Generates the related routes.

Routing Definition
------------------

Route names
^^^^^^^^^^^

You can set a ``baseRouteName`` property inside your ``Admin`` class. This
represents the route prefix, to which an underscore and the action name will
be added to generate the actual route names. Note: this is the internal *name*
given to a route (it has nothing to do with the route's visible *URL*).

.. code-block:: php

    <?php
    class PostAdmin extends Admin
    {
        protected $baseRouteName = 'sonata_post';
        // will result in routes named:
        //   sonata_post_list
        //   sonata_post_create
        //   etc..
    }

If no ``baseRouteName`` is defined then the Admin will generate one for you,
based on the following format: 'admin_vendor_bundlename_entityname' so you will have
route names for your actions like 'admin_vendor_bundlename_entityname_list'.

If the Admin fails to find a baseRouteName for your Admin class a ``RuntimeException``
will be thrown with a related message.

Route patterns (URLs)
^^^^^^^^^^^^^^^^^^^^^

You can use ``baseRoutePattern`` to set a custom URL for a given ``Admin`` class.

For example, to use ``http://yourdomain.com/admin/foo`` as the base URL for
the ``FooAdmin`` class (instead of the default of ``http://yourdomain.com/admin/vendor/bundle/foo``)
use the following code:

.. code-block:: php

    <?php
    class FooAdmin extends Admin
    {
        protected $baseRoutePattern = 'foo';
    }

You will then have route URLs like ``http://yourdomain.com/admin/foo/list`` and
``http://yourdomain.com/admin/foo/1/edit``

Routing usage
-------------

Inside a CRUD template, a route for the current ``Admin`` class can be generated via
the admin variable's ``generateUrl()`` command:

.. code-block:: html+jinja

    <a href="{{ admin.generateUrl('list') }}">List</a>

    <a href="{{ admin.generateUrl('list', params|merge('page': 1)) }}">List</a>

Note that you do not need to provide the Admin's route prefix (baseRouteName) to
generate a URL for the current Admin, just the action name.

To generate a URL for a different Admin you just use the Route Name with the usual
Twig helpers:

.. code-block:: html+jinja

    <a href="{{ path('admin_acme_demo_foo_list') }}">List</a>


Create a route
--------------

You can register new routes by defining them in your ``Admin`` class. Only Admin
routes should be registered this way.

The routes you define in this way are generated within your Admin's context, and
the only required parameter to ``add()`` is the action name. The second parameter
can be used to define the URL format to append to ``baseRoutePattern``, if not set
explicitly this defaults to the action name.

.. code-block:: php

    <?php
    use Sonata\AdminBundle\Route\RouteCollection;

    class MediaAdmin extends Admin
    {
        protected function configureRoutes(RouteCollection $collection)
        {
            $collection->add('myCustom'); // Action gets added automatically
            $collection->add('view', $this->getRouterIdParameter().'/view');
        }
    }

Other steps needed to create your new action
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

In addition to defining the route for your new action you also need to create a
handler for it in your Controller. By default Admin classes use ``SonataAdminBundle:CRUD``
as their controller, but this can be changed by altering the third argument when defining
your Admin service (in your admin.yml file).

For example, lets change the Controller for our MediaAdmin class to AcmeDemoBundle:MediaCRUD:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/DemoBundle/Resources/config/admin.yml
        sonata.admin.media:
            class: Acme\DemoBundle\Admin\MediaAdmin
            tags:
                - { name: sonata.admin, manager_type: orm, label: "Media" }
            arguments:
                - ~
                - Acme\DemoBundle\Entity\Page
                - 'AcmeDemoBundle:MediaCRUD' # define the new controller via the third argument
            calls:
                - [ setTranslationDomain, [Acme\DemoBundle]]

We now need to create our Controller, the easiest way is to extend the basic Sonata CRUD controller:

.. code-block:: php

    <?php
    // /src/Acme/DemoBundle/Controller/MediaCRUDController.php
    namespace Acme\DemoBundle\Controller;

    use Sonata\AdminBundle\Controller\CRUDController;

    class MediaCRUDController extends CRUDController
    {
        public function myCustomAction()
        {
            // your code here ...
        }
    }

Removing a route
----------------

Extending ``Sonata\AdminBundle\Admin\Admin`` will give your Admin classes the following
default routes:

* batch
* create
* delete
* export
* edit
* list
* show

You can view all of the current routes defined for an Admin class by using the console to run

``php app/console sonata:admin:explain <<admin.service.name>>``

for example if your Admin is called sonata.admin.foo you would run

``php app/console sonata:admin:explain sonata.admin.foo``

Sonata internally checks for the existence of a route before linking to it. As a result, removing a
route will prevent links to that action from appearing in the administrative interface. For example,
removing the 'create' route will prevent any links to "Add new" from appearing.

Removing a single route
^^^^^^^^^^^^^^^^^^^^^^^

Any single registered route can be easily removed by name:

.. code-block:: php

    <?php
    use Sonata\AdminBundle\Route\RouteCollection;

    class MediaAdmin extends Admin
    {
        protected function configureRoutes(RouteCollection $collection)
        {
            $collection->remove('delete');
        }
    }


Removing all routes except named ones
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you want to disable all default Sonata routes except few whitelisted ones, you can use
the ``clearExcept()`` method. This method accepts an array of routes you want to keep active.

.. code-block:: php

    <?php
    use Sonata\AdminBundle\Route\RouteCollection;

    class MediaAdmin extends Admin
    {
        protected function configureRoutes(RouteCollection $collection)
        {
            // Only `list` and `edit` route will be active
            $collection->clearExcept(array('list', 'edit'));
        }
    }

Removing all routes
^^^^^^^^^^^^^^^^^^^

If you want to remove all default routes, you can use ``clear()`` method.

.. code-block:: php

    <?php
    use Sonata\AdminBundle\Route\RouteCollection;

    class MediaAdmin extends Admin
    {
        protected function configureRoutes(RouteCollection $collection)
        {
            // All routes are removed
            $collection->clear();
        }
    }

Removing routes only when an Admin is embedded
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

To prevent some routes from being available when one Admin is embedded inside another one
(e.g. to remove the "add new" option when you embed ``TagAdmin`` within ``PostAdmin``) you
can use ``hasParentFieldDescription()`` to detect this case and remove the routes.

.. code-block:: php

    <?php
    use Sonata\AdminBundle\Route\RouteCollection;

    class TagAdmin extends Admin
    {
        protected function configureRoutes(RouteCollection $collection)
        {
            if ($this->hasParentFieldDescription()) { // prevent display of "Add new" when embedding this form
                $collection->remove('create');
            }
        }
    }

Persistent parameters
---------------------

In some cases, the interface might be required to pass the same parameters
across the different ``Admin``'s actions. Instead of setting them in the
template or doing other weird hacks, you can define a ``getPersistentParameters``
method. This method will be used when a link is being generated.

.. code-block:: php

    <?php
    class MediaAdmin extends Admin
    {
        public function getPersistentParameters()
        {
            if (!$this->getRequest()) {
                return array();
            }

            return array(
                'provider' => $this->getRequest()->get('provider'),
                'context'  => $this->getRequest()->get('context', 'default'),
            );
        }
    }

    // the result:
    // $admin->generateUrl('create') => /admin/module/create?context=default

Changing the default route in a List Action
-------------------------------------------

Usually the identifier column of a list action links to the edit screen. To change the
list action's links to point to a different action, set the ``route`` option in your call to
``ListMapper::addIdentifier()``. For example, to link to show instead of edit:

.. code-block:: php

    <?php
    class PostAdmin extends Admin
    {
        public function configureListFields(ListMapper $listMapper)
        {
            $listMapper
                ->addIdentifier('name', null, array(
                    'route' => array('name' => 'show')
                ));
        }
    }
