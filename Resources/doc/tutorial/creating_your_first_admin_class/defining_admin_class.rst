Defining admin class
====================

The admin class contains all the information required to generate the CRUD
interface. Let's create the Post Admin class.

PostAdmin
---------

By convention, Admin files are located in an Admin namespace.

First, you need to create an Admin/PostAdmin.php file

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;

    use Sonata\NewsBundle\Entity\Comment;

    class PostAdmin extends Admin
    {
        protected $list = array(
            'title' => array('identifier' => true),
            'author',
            'enabled',
            'commentsEnabled',
        );

        protected $form = array(
            'author'  => array('edit' => 'list'),
            'enabled' => array('form_field_options' => array('required' => false)),
            'title',
            'abstract',
            'content',
            'tags'     => array('form_field_options' => array('expanded' => true)),
            'commentsCloseAt',
            'commentsEnabled' => array('form_field_options' => array('required' => false)),
        );

        protected $filter = array(
            'title',
            'enabled',
            'tags' => array('filter_field_options' => array('expanded' => true, 'multiple' => true))
        );

        public function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
              ->add('author')
              ->add('image', array(), array('edit' => 'list', 'link_parameters' => array('context' => 'news')))
              ->add('commentsDefaultStatus', array('choices' => Comment::getStatusList()), array('type' => 'choice'));
        }
    }

Second, register the PostAdmin class inside the DIC in your config file:

.. code-block:: xml

    <service id="sonata.news.admin.post" class="Sonata\NewsBundle\Admin\PostAdmin">
        <tag name="sonata.admin" manager_type="orm" group="sonata_blog" label="post"/>

        <argument/>
        <argument>Sonata\NewsBundle\Entity\Post</argument>
        <argument>SonataNewsBundle:PostAdmin</argument>
    </service>

Or if you're using a YML configuration file:

.. code-block:: yaml

    services:
       sonata.news.admin.post:
          class: Sonata\NewsBundle\Admin\PostAdmin
          tags:
            - { name: sonata.admin, manager_type: orm, group: sonata_blog, label: post }
          arguments: [null, Sonata\NewsBundle\Entity\Post, SonataNewsBundle:PostAdmin]

These is the minimal configuration required to display the entity inside the
dashboard and interact with the CRUD interface. Following this however, you will
need to create an admin Controller.

This interface will display too many fields as some of them are not relevant to
a general overview. Next We'll see how to specify the fields we want to use and
how we want to use them.

Tweak the PostAdmin class
-------------------------

You can specify which field you want displayed for each action (list, form and filter)

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;

    use Knp\Bundle\MenuBundle\MenuItem;

    use Application\Sonata\NewsBundle\Entity\Comment;
    
    class PostAdmin extends Admin
    {
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
           'tags' => array('form_field_options' => array('expanded' => true)),
           'comments_enabled',
           'comments_default_status'
       );

       protected $filter = array(
           'title',
           'enabled',
           'tags' => array('filter_field_options' => array('expanded' => true, 'multiple' => true))
       );
     }

Now the different CRUD interfaces will look nicer!

So same goes for the TagAdmin and CommentAdmin class.

Tweak the TagAdmin class
------------------------

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;

    class TagAdmin extends Admin
    {
        protected $list = array(
            'name' => array('identifier' => true),
            'slug',
            'enabled',
        );

        protected $form = array(
            'id',
            'name',
            'enabled'
        );

        protected $filter = array(
            'name'
        );
    }

Tweak the CommentAdmin class
----------------------------

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;

    use Sonata\NewsBundle\Entity\Comment;

    class CommentAdmin extends Admin
    {
        protected $list = array(
            'name' => array('identifier' => true),
            'getStatusCode' => array('label' => 'status_code', 'type' => 'string', 'sortable' => 'status'),
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
        );

        protected $filter = array(
            'name',
            'email',
            'message'
        );

        public function configureFormFields(FormMapper $form)
        {
            $form->add('status', array('choices' => Comment::getStatusList()), array('type' => 'choice'));
        }
    }
