Defining admin class
====================


The admin class contains all information required to generate the CRUD interface.
Let's create the Post Admin class.


PostAdmin
---------

By convention Admin files are set under a Admin folder.

First, you need to create an Admin/PostAdmin.php file

.. code-block:: php

    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;

    class PostAdmin extends Admin
    {

    }

Secondly, register the PostAdmin class inside the DIC in your config.xml file.

.. code-block:: xml

    <service id="sonata.news.admin.post" class="Sonata\NewsBundle\Admin\PostAdmin">

        <tag name="sonata.admin" manager_type="orm" group="sonata_blog" label="post"/>

        <argument/>
        <argument>Sonata\NewsBundle\Entity\Post</argument>
        <argument>SonataNewsBundle:PostAdmin</argument>
    </service>

Or if you're using an YML configuration file,

.. code-block:: yml

    services:
       sonata.news.admin.post:
          class: Sonata\NewsBundle\Admin\PostAdmin
          tags:
            - { name: sonata.admin, manager_type: orm, group: sonata_blog, label: post }
          arguments: [null, Sonata\NewsBundle\Entity\Post, SonataNewsBundle:PostAdmin]

These is the minimal configuration required to display the entity inside the dashboard and
interact with the CRUD interface. However, you need to create your admin Controller.
The interface will display too many fields as some of them are not meant to be displayed.
We'll see how we can specify the differents fields we want to use

Tweak the PostAdmin class
-------------------------

You can specify which field you want displayed for each action (list, form and filter)

.. code-block:: php

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
--------

.. code-block:: php

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
            'name',
            'enabled'
        );
    }

Tweak the CommentAdmin class
------------

.. code-block:: php

    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;

    class CommentAdmin extends Admin
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
