Filter Field Definition
=======================

These fields are displayed inside the filter box. They allow you to filter
the list of entities by a number of different methods.

Example
-------

.. code-block:: php

    <?php
    namespace Bundle\Sonata\NewsBundle\Admin;

    use Bundle\Sonata\BaseApplicationBundle\Admin\Admin;

    class PostAdmin extends Admin
    {

        protected $class = 'Application\Sonata\NewsBundle\Entity\Post';

        protected $filterFields = array(
            'title',
            'enabled',
            'tags' => array('filter_field_options' => array('expanded' => true, 'multiple' => true))
        );

        public function configureFilterFields()
        {
            $this->filterFields['with_open_comments'] = new FieldDescription;
            $this->filterFields['with_open_comments']->setName('label');
            $this->filterFields['with_open_comments']->setTemplate('Sonata\BaseApplicationBundle:CRUD:filter_callback.twig');
            $this->filterFields['with_open_comments']->setType('callback');
            $this->filterFields['with_open_comments']->setOption('filter_options', array(
                'filter' => array($this, 'getWithOpenCommentFilter'),
                'field'  => array($this, 'getWithOpenCommentField')
            ));
        }

        public function getWithOpenCommentFilter($query_builder, $alias, $field, $value)
        {

            if (!$value) {
                return;
            }

            $query_builder->leftJoin(sprintf('%s.comments', $alias), 'c');
            $query_builder->andWhere('c.status = :status');
            $query_builder->setParameter('status', \Application\Sonata\NewsBundle\Entity\Comment::STATUS_MODERATE);
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

