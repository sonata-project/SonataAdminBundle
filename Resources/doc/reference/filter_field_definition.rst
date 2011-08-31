Filter Field Definition
=======================

These fields are displayed inside the filter box. They allow you to filter
the list of entities by a number of different methods.

A filter instance is always linked to a Form Type, there is 3 types availables :

  - sonata_type_filter_number  :  display 2 widgets, the operator ( >, >=, <= , <, =) and the value
  - sonata_type_filter_choice  :  display 2 widgets, the operator (yes and no) and the value
  - sonata_type_filter_default :  display 2 widgets, an hidden operator (can be changed on demand) and the value
  - sonata_type_filter_date ( not implemented yet )

The Form Type configuration is provided by the filter itself. But they can be tweaked in the ``configureDatagridFilters``
process with the ``add`` method.

The ``add`` method accepts 4 arguments :

  - the field name
  - the filter type     : the filter name
  - the filter options  : the options related to the filter
  - the field type      : the type of widget used to render the value part
  - the field options   : the type options

Type available
---------------

For now, only Doctrine ORM filters are available

  - doctrine_orm_boolean   : depends on the ``sonata_type_filter_default`` Form Type, render yes or no field
  - doctrine_orm_callback  : depends on the ``sonata_type_filter_default`` Form Type, types can be configured as needed
  - doctrine_orm_choice    : depends on the ``sonata_type_filter_choice`` Form Type, render yes or no field
  - doctrine_orm_model     : depends on the ``sonata_type_filter_number`` Form Type
  - doctrine_orm_string    : depends on the ``sonata_type_filter_choice``
  - doctrine_orm_number    : depends on the ``sonata_type_filter_choice`` Form Type, render yes or no field


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
                ->add('tags', null, array(), null, array('expanded' => true, 'multiple' => true)
            ;
        }
    }


Advanced usage
--------------

Label
^^^^^

You can customize the label which appears on the main widget by using a ``label`` option.

.. code-block:: php

    <?php

    ->add('tags', null, array('label' => 'les tags'), null, array('expanded' => true, 'multiple' => true)


Callback
^^^^^^^^

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
                ->add('tags', null, array(), null, array('expanded' => true, 'multiple' => true))
                ->add('author')
                ->add('with_open_comments', 'doctrine_orm_callback', array(
    //                'callback'   => array($this, 'getWithOpenCommentFilter'),
                    'callback' => function($queryBuilder, $alias, $field, $value) {
                        if (!$value) {
                            return;
                        }

                        $queryBuilder->leftJoin(sprintf('%s.comments', $alias), 'c');
                        $queryBuilder->andWhere('c.status = :status');
                        $queryBuilder->setParameter('status', Comment::STATUS_MODERATE);
                    },
                    'field_type' => 'checkbox'
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
