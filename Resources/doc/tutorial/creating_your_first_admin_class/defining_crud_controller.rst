Defining the CRUD controller
===========================

A crud controller class is just an empty class with no methods. However, you can easily add here
new action or overwrite the default CRUD actions.

Just create 3 files inside the Controller directory


- CommendAdminController.php

..

    namespace Bundle\NewsBundle\Controller;

    use Bundle\Sonata\BaseApplicationBundle\Controller\CRUDController as Controller;
    
    class TagAdminController extends Controller
    {

    }

- PostAdminController.php

..

    namespace Bundle\NewsBundle\Controller;

    use Bundle\Sonata\BaseApplicationBundle\Controller\CRUDController as Controller;

    class PostAdminController extends Controller
    {

    }

- TagAdminController.php

..

    namespace Bundle\NewsBundle\Controller;

    use Bundle\Sonata\BaseApplicationBundle\Controller\CRUDController as Controller;

    class TagAdminController extends Controller
    {

    }


When the controller class is instanciated, the admin class is attached to the controller.

Let's create the admin classes ...