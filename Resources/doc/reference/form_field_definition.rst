Form field definition
=====================

These fields are used to display inside the edit form.

Example
-------

.. code-block:: php

    <?php
    namespace Sonta\NewsBundle\Admin;

    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Admin\EntityAdmin;

    class PostAdmin extends EntityAdmin
    {
        protected $form = array(
            'author' => array('edit' => 'list'),
            'enabled',
            'title',
            'abstract',
            'content',
            'tags' => array('options' => array('expanded' => true)),
            'comments_close_at',
            'comments_enabled',
            'comments_default_status'
        );

        public function configureFormFields(FormMapper $form)
        {
            $form->add('author', array(), array('edit' => 'list'));
            $form->add('title');

            // add comments_default_status by configuring an internal FieldDescription
            $form->add('comments_default_status', array('choices' => Comment::getStatusList()), array('type' => 'choice'));

            // or by creating the FormField
            $form->add(new \Symfony\Component\Form\ChoiceField('comments_default_status', array('choices' => Comment::getStatusList())));
        }
    }

Types available
---------------

- array
- boolean
- choice
- datetime
- decimal
- integer
- many_to_many
- many_to_one
- one_to_one
- string
- text

if no type is set, the Admin class will use the one set in the doctrine mapping definition.

Tweak it!
---------

- It is possible to tweak the default template by setting a template key in the
- If the project required specific behaviors, they can be implemented in the
configureFormFields() method.

