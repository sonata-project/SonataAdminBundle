Filter Field Definition
=======================

These fields are displayed inside the filter box. They allow you to filter
the list of entities by a number of different methods.

Example
-------

.. code-block:: php

    <?php
    namespace Bundle\Sonata\NewsBundle\Admin;

    use Sonata\BaseApplicationBundle\Datagrid\DatagridMapper;
    use Bundle\Sonata\BaseApplicationBundle\Admin\Admin;

    class PostAdmin extends Admin
    {

        protected $class = 'Application\Sonata\NewsBundle\Entity\Post';

        protected $filter = array(
            'title',
            'enabled',
            'tags' => array('filter_field_options' => array('expanded' => true, 'multiple' => true))
        );

        public function configureDatagridFilters(DatagridMapper $datagrid)
        {

            $datagrid->add('with_open_comments', array(
                'template' => 'SonataBaseApplicationBundle:CRUD:filter_callback.twig.html',
                'type' => 'callback',
                'filter_options' => array(
                    'filter' => array($this, 'getWithOpenCommentFilter'),
                    'field'  => array($this, 'getWithOpenCommentField')
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
            $queryBuilder->setParameter('status', \Application\Sonata\NewsBundle\Entity\Comment::STATUS_MODERATE);
        }

        public function getWithOpenCommentField($filter)
        {

            return new \Symfony\Component\Form\CheckboxField(
                $filter->getName(),
                array()
            );
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

- It is possible to tweak the default template by setting a template key in the
- If the project required specific behaviors, they can be implemented in the
configureFilterFields() method.

