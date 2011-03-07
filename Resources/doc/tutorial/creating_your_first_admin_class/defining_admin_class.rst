Defining admin class
====================


The admin class contains all information required to generate the CRUD interface.

Let's create the Post Admin class.


PostAdmin
---------

By convention Admin files are set under a Admin folder.


- Create an Admin/PostAdmin.php file

..

    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\EntityAdmin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;

    class PostAdmin extends EntityAdmin
    {

    }

- register the PostAdmin class inside the DIC

..

    <service id="sonata.news.admin.post" class="Sonata\NewsBundle\Admin\PostAdmin">

        <tag name="sonata.admin" manager_type="orm" group="sonata_blog" label="post"/>

        <argument>Application\Sonata\NewsBundle\Entity\Post</argument>
        <argument>SonataNewsBundle:PostAdmin</argument>
    </service>


These is the minimal configuration required to display the entity inside the dashboard and
interact with the CRUD interfance. However the interface will display too many fields as some
of them are not meant to be displayed.

Tweak the PostAdmin class
-------------------------

Now, let's specify the differents fields we want to use:
 

..

    protected $list = array(
        'title' => array('identifier' => true),
        'slug',
        'enabled',
        'comments_enabled',
    );

    protected $form = array(
        'enabled',
        'title',
        'abstract',
        'content',
        'tags' => array('options' => array('expanded' => true)),
        'comments_enabled',
        'comments_default_status'
    );

    protected $filter = array(
        'title',
        'enabled',
        'tags' => array('filter_field_options' => array('expanded' => true, 'multiple' => true))
    );


Now the different CRUD interfaces will look nicer!


So same goes for the TagAdmin and CommentAdmin class.

TagAdmin
--------

..

    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\EntityAdmin;

    class TagAdmin extends EntityAdmin
    {

        protected $list = array(
            'name' => array('identifier' => true),
            'slug',
            'enabled',
        );

        protected $form = array(
            'name',
            'enabled'
        );
    }

CommentAdmin
------------

..

    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\EntityAdmin;

    class CommentAdmin extends EntityAdmin
    {
        protected $list = array(
            'name' => array('identifier' => true),
            'getStatusCode' => array('label' => 'status_code'),
            'post',
            'email',
            'url',
            'message',
        );

        protected $form = array(
            'name',
            'email',
            'url',
            'message',
            'post',
            'status' => array('type' => 'choice'),
        );
    }