Update notes for early users
============================

* Property definitions has been removed
* ``[Form|List|Datagrid|Show]Mapper::add`` signature has been updated
* ``FormMapper::addType`` does not exists anymore
* ListMapper now have an ``addIdentifier`` method
* internal Sonata Form Types must be set as second argument of the FormMapper

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
        protected $userManager;

        protected function configureShowField(ShowMapper $showMapper)
        {
            $showMapper
                ->add('author')
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
                    ->add('author', 'sonata_type_model', array(), array('edit' => 'list'))
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
                ->add('author')
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
    }