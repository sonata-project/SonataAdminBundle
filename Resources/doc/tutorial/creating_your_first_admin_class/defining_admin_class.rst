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
    use Sonata\AdminBundle\Show\ShowMapper;

    use Knp\Menu\ItemInterface as MenuItemInterface;

    use Application\Sonata\NewsBundle\Entity\Comment;

    class PostAdmin extends Admin
    {
        protected function configureShowField(ShowMapper $showMapper)
        {
            $showMapper
                ->add('enabled')
                ->add('title')
                ->add('abstract')
                ->add('content')
                ->add('tags')
            ;
        }

        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->with('General')
                    ->add('enabled', null, array('required' => false))
                    ->add('title')
                    ->add('abstract')
                    ->add('content')
                ->end()
                ->with('Tags')
                    ->add('tags', 'sonata_type_model', array('expanded' => true))
                ->end()
                ->with('Options', array('collapsed' => true))
                    ->add('commentsCloseAt')
                    ->add('commentsEnabled', null, array('required' => false))
                    ->add('commentsDefaultStatus', 'choice', array('choices' => Comment::getStatusList()))
                ->end()
            ;
        }

        protected function configureListFields(ListMapper $listMapper)
        {
            $listMapper
                ->addIdentifier('title')
                ->add('enabled')
                ->add('tags')
                ->add('commentsEnabled')
            ;
        }

        protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        {
            $datagridMapper
                ->add('title')
                ->add('enabled')
                ->add('tags', 'orm_many_to_many', array('filter_field_options' => array('expanded' => true, 'multiple' => true)))
                ->add('with_open_comments', 'callback', array(
                    'template' => 'SonataAdminBundle:CRUD:filter_callback.html.twig',
                    'filter_options' => array(
                        'filter' => array($this, 'getWithOpenCommentFilter'),
                        'type'   => 'checkbox'
                    ),
                    'filter_field_options' => array(
                        'required' => false
                    )
                ))
            ;
        }

        public function getWithOpenCommentFilter($queryBuilder, $alias, $field, $value)
        {
            if (!$value) {
                return;
            }

            $queryBuilder->leftJoin(sprintf('%s.comments', $alias), 'c');
            $queryBuilder->andWhere('c.status = :status');
            $queryBuilder->setParameter('status', Comment::STATUS_MODERATE);
        }

        protected function configureSideMenu(MenuItemInterface $menu, $action, Admin $childAdmin = null)
        {
            if (!$childAdmin && !in_array($action, array('edit'))) {
                return;
            }

            $admin = $this->isChild() ? $this->getParent() : $this;

            $id = $admin->getRequest()->get('id');

            $menu->addChild(
                $this->trans('view_post'),
                array('uri' => $admin->generateUrl('edit', array('id' => $id)))
            );

            $menu->addChild(
                $this->trans('link_view_comment'),
                array('uri' => $admin->generateUrl('sonata.news.admin.comment.list', array('id' => $id)))
            );
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

So same goes for the TagAdmin and CommentAdmin class.

Tweak the TagAdmin class
------------------------

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Validator\ErrorElement;
    use Sonata\AdminBundle\Form\FormMapper;

    class TagAdmin extends Admin
    {
        /**
         * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
         * @return void
         */
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('name')
                ->add('enabled', null, array('required' => false))
            ;
        }

        /**
         * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $datagridMapper
         * @return void
         */
        protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        {
            $datagridMapper
                ->add('name')
                ->add('posts')
            ;
        }

        /**
         * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
         * @return void
         */
        protected function configureListFields(ListMapper $listMapper)
        {
            $listMapper
                ->addIdentifier('name')
                ->add('slug')
                ->add('enabled')
            ;
        }

        /**
         * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
         * @param $object
         * @return void
         */
        public function validate(ErrorElement $errorElement, $object)
        {
            $errorElement
                ->with('name')
                    ->assertMaxLength(array('limit' => 32))
                ->end()
            ;
        }
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

    use Application\Sonata\NewsBundle\Entity\Comment;

    class CommentAdmin extends Admin
    {
        protected $parentAssociationMapping = 'post';

        /**
         * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
         * @return void
         */
        protected function configureFormFields(FormMapper $formMapper)
        {
            if(!$this->isChild()) {
                $formMapper->add('post', 'sonata_type_model', array(), array('edit' => 'list'));
    //            $formMapper->add('post', 'sonata_type_admin', array(), array('edit' => 'inline'));
            }

            $formMapper
                ->add('name')
                ->add('email')
                ->add('url', null, array('required' => false))
                ->add('message')
                ->add('status', 'choice', array('choices' => Comment::getStatusList(), 'expanded' => true, 'multiple' => false))
            ;
        }

        /**
         * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $datagridMapper
         * @return void
         */
        protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        {
            $datagridMapper
                ->add('name')
                ->add('email')
                ->add('message')
            ;
        }

        /**
         * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
         * @return void
         */
        protected function configureListFields(ListMapper $listMapper)
        {
            $listMapper
                ->addIdentifier('name')
                ->add('getStatusCode', 'text', array('label' => 'status_code', 'sortable' => 'status'))
                ->add('post')
                ->add('email')
                ->add('url')
                ->add('message');
        }

        /**
         * @return array
         */
        public function getBatchActions()
        {
            $actions = parent::getBatchActions();

            $actions['enabled'] = array(
                'label' => $this->trans('batch_enable_comments'),
                'ask_confirmation' => false,
            );

            $actions['disabled'] = array(
                'label' => $this->trans('batch_disable_comments'),
                'ask_confirmation' => false
            );

            return $actions;
        }
    }
