Form Help Messages
==========

About Help messages
------------------------

Help messages are short notes that are rendered together with form fields. They are generally used to show additional information so the user can complete the form-element faster and more accurately.

Example
----------------

.. code-block:: php

    <?php
    class ExampleAdmin.php
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->with('General')
                    ->add('title', null, array('help'=>'Set the title of a web page'))
                    ->add('keywords', null, array('help'=>'Set the keywords of a web page'))
                ->end();
        }
    }

Alternative way to define help message
----------------

.. code-block:: php

    <?php
    class ExampleAdmin.php
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->with('General')
                    ->add('title')
                    ->add('keywords')
                    ->setHelps(array(
                        'title' => 'Set the title of a web page',
                        'keywords' => 'Set the keywords of a web page',
                    ))
                ->end();
        }
    }

