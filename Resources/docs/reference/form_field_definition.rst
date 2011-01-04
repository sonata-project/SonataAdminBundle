Form field definition
=====================

These fields are used to display inside the edit form.

Example
-------

.. code-block:: php

    <?php
    namespace Bundle\NewsBundle\Admin;

    use Bundle\Sonata\BaseApplicationBundle\Admin\Admin;

    class PostAdmin extends Admin
    {

        protected $class = 'Application\NewsBundle\Entity\Post';

        protected $form_fields = array(
            'enabled',
            'title',
            'abstract',
            'content',
            'tags' => array('options' => array('expanded' => true)),
            'comments_close_at',
            'comments_enabled',
            'comments_default_status'
        );

        public function configureFormFields()
        {
            $this->form_fields['comments_default_status']['type'] = 'choice';
            $this->form_fields['comments_default_status']['options']['choices'] = \Application\NewsBundle\Entity\Comment::getStatusList();
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

