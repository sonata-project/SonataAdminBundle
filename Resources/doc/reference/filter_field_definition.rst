Filter Field Definition
=======================

These fields are displayed inside the filter box. They allow you to filter
the list of entities by a number of different methods.

Example
-------

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Admin\Admin;

    class PostAdmin extends Admin
    {
        protected $filter = array(
            'title',
            'enabled',
            'tags' => array('filter_field_options' => array('expanded' => true, 'multiple' => true))
        );

        public function configureDatagridFilters(DatagridMapper $datagrid)
        {
            // this is equivalent to :
            $datagrid->add('title');
            $datagrid->add('enabled');
            $datagrid->add('tags', array('filter_field_options' => array('expanded' => true, 'multiple' => true))
        }
    }

Types available
---------------

- boolean
- callback
- decimal
- identifier
- integer
- many_to_many
- string
- text

if no type is set, the Admin class will use the one set in the doctrine mapping definition.

Tweak it!
---------

- It is possible to tweak the default template by setting a ``template`` key in the 
  options array.
- If the project required specific behaviors, they can be implemented in the
  ``configureFilterFields()`` method.
  
Advanced usage
--------------

If you want to create a custom callback filter, this is how to do. ``getWithOpenCommentField`` and
``getWithOpenCommentFilter``  are callback method used to define the field type and how to filter 
the provided value.

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Admin\Admin;
    use Application\Sonata\NewsBundle\Entity\Comment;
    
    class PostAdmin extends Admin
    {
        protected $filter = array(
            'title',
            'enabled',
            'tags' => array('filter_field_options' => array('expanded' => true, 'multiple' => true))
        );

        public function configureDatagridFilters(DatagridMapper $datagrid)
        {
            $datagrid->add('with_open_comments', array(
                'template' => 'SonataAdminBundle:CRUD:filter_callback.html.twig',
                'type' => 'callback',
                'filter_options' => array(
                    'filter' => array($this, 'getWithOpenCommentFilter'),
                    'type'   => 'checkbox'
                ),
                'filter_field_options' => array(
                    'required' => false
                )
            ));
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
