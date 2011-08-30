Defining the CRUD controller
============================

A crud controller class is just an empty class with no methods. However, you can
add new actions or overwrite the default CRUD actions to suit your application.

::

    The controller declaration is optional, if none is defined, then the ``AdminBundle`` will use
    the ``CRUDController``.

Just create 3 files inside the Controller directory

CommentAdminController
~~~~~~~~~~~~~~~~~~~~~~

::

    <?php
    namespace Sonata\NewsBundle\Controller;

    use Sonata\AdminBundle\Controller\CRUDController as Controller;
    
    class CommentAdminController extends Controller
    {

    }

PostAdminController
~~~~~~~~~~~~~~~~~~~

::

    <?php
    namespace Sonata\NewsBundle\Controller;

    use Sonata\AdminBundle\Controller\CRUDController as Controller;

    class PostAdminController extends Controller
    {

    }

TagAdminController
~~~~~~~~~~~~~~~~~~

::

    <?php
    namespace Sonata\NewsBundle\Controller;

    use Sonata\AdminBundle\Controller\CRUDController as Controller;

    class TagAdminController extends Controller
    {

    }

When the controller class is instantiated, the admin class is attached to the
controller.

Let's create the admin classes ...
