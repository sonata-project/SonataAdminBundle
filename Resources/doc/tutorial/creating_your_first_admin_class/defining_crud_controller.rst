Defining the CRUD controller
============================

A crud controller class is just an empty class with no methods. However, you can easily add here
new action or overwrite the default CRUD actions.

.. note::
  
    The controller declaration is optional, if none is defined, then the ``AdminBundle`` will use
    the ``CRUDController``.

Just create 3 files inside the Controller directory

CommendAdminController
~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Controller;

    use Sonata\AdminBundle\Controller\CRUDController as Controller;
    
    class CommentAdminController extends Controller
    {

    }

PostAdminController
~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Controller;

    use Sonata\AdminBundle\Controller\CRUDController as Controller;

    class PostAdminController extends Controller
    {

    }

TagAdminController
~~~~~~~~~~~~~~~~~~

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Controller;

    use Sonata\AdminBundle\Controller\CRUDController as Controller;

    class TagAdminController extends Controller
    {

    }


When the controller class is instanciated, the admin class is attached to the controller.

Let's create the admin classes ...