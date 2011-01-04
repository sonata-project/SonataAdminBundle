Defining admin class
====================


The admin class contains all information required to generate the CRUD interface.

Let's create the Post Admin class.


PostAdmin
---------

By convention Admin files are set under a Admin folder.


- Create an Admin/PostAdmin.php file

..

    namespace Application\NewsBundle\Admin;

    use Bundle\Sonata\BaseApplicationBundle\Admin\Admin;

    class PostAdmin extends Admin
    {

        protected $class = 'Application\NewsBundle\Entity\Post';

        protected $base_route = 'news_post_admin';

        protected $base_controller_name = 'NewsBundle:PostAdmin';

    }

- register the AdminClass inside the base_application.config from the config.yml file

..

    post:

        class:      Bundle\NewsBundle\Admin\PostAdmin

        entity:     Application\NewsBundle\Entity\Post

        controller: Bundle\NewsBundle\Controller\PostAdminController



These is the minimal configuration required to display the entity inside the dashboard and
interact with the CRUD interfance. However the interface will display to many fields as some
of them are not mean to be displayed.

Tweak the PostAdmin class
-------------------------

Now, let's specify the differents we want to use:
 

..

    protected $list_fields = array(
        'title' => array('identifier' => true),
        'slug',
        'enabled',
        'comments_enabled',
    );

    protected $form_fields = array(
        'enabled',
        'title',
        'abstract',
        'content',
        'tags' => array('options' => array('expanded' => true)),
        'comments_enabled',
        'comments_default_status'
    );

    protected $filter_fields = array(
        'title',
        'enabled',
        'tags' => array('filter_field_options' => array('expanded' => true, 'multiple' => true))
    );


Now the different CRUD interfaces will look nicer!


So same goes for the TagAdmin and CommentAdmin class.

TagAdmin
--------

..

    namespace Bundle\NewsBundle\Admin;

    use Bundle\Sonata\BaseApplicationBundle\Admin\Admin;

    class TagAdmin extends Admin
    {
        protected $class = 'Application\NewsBundle\Entity\Tag';

        protected $list_fields = array(
            'name' => array('identifier' => true),
            'slug',
            'enabled',
        );

        protected $form_fields = array(
            'name',
            'enabled'
        );

        protected $base_route = 'news_tag_admin';

        protected $base_controller_name = 'NewsBundle:TagAdmin';
    }

CommentAdmin
------------

..

    namespace Bundle\NewsBundle\Admin;

    use Bundle\Sonata\BaseApplicationBundle\Admin\Admin;

    class CommentAdmin extends Admin
    {

        protected $class = 'Application\NewsBundle\Entity\Comment';

        protected $list_fields = array(
            'name' => array('identifier' => true),
            'getStatusCode' => array('label' => 'status_code'),
            'post',
            'email',
            'url',
            'message',
        );

        protected $form_fields = array(
            'name',
            'email',
            'url',
            'message',
            'post',
            'status' => array('type' => 'choice'),
        );

        protected $base_route = 'news_comment_admin';

        protected $base_controller_name = 'NewsBundle:CommentAdmin';
    }