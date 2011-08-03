Filter Field Definition
=======================

These fields are displayed inside the filter box. They allow you to filter
the list of entities by a number of different methods.

Example
-------

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\AdminBundle\Show\ShowMapper;
    use Sonata\AdminBundle\Validator\ErrorElement;

    class PostAdmin extends Admin
    {
        protected function configureDatagridFilters(DatagridMapper $datagrid)
        {
            $datagrid
                ->add('title');
                ->add('enabled');
                ->add('tags', null, array('filter_field_options' => array('expanded' => true, 'multiple' => true))
            ;
        }
    }

Types available
---------------

- checkbox
- callback
- decimal
- identifier
- integer
- text

If no type is set, the Admin class will use the one set in the Doctrine mapping
definition.

Tweak it!
---------

- It is possible to change the default template by setting a ``template`` key
  in the options array.
- If the project requires specific behaviors, they can be implemented in the
  ``configureFilterFields()`` method.

Advanced usage
--------------

To create a custom callback filter, two methods need to be implemented; one to
define the field type and one to define how to use the field's value. In this
example, ``getWithOpenCommentField`` and ``getWithOpenCommentFilter`` implement
this functionality.

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\AdminBundle\Show\ShowMapper;
    use Sonata\AdminBundle\Validator\ErrorElement;

    use Application\Sonata\NewsBundle\Entity\Comment;

    class PostAdmin extends Admin
    {
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
