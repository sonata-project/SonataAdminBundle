Form Help Messages and Descriptions
===================================

Help Messages
-------------

Help messages are short notes that are rendered together with form fields. They are generally used to show additional information so the user can complete the form element faster and more accurately. The text is not escaped, so HTML can be used.

Example
^^^^^^^

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

Alternative Way To Define Help Messages
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

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

Form Group Descriptions
-----------------------

A form group description is a block of text rendered below the group title. These can be used to describe a section of a form. The text is not escaped, so HTML can be used.

Example
^^^^^^^

.. code-block:: php

    <?php
    class ExampleAdmin.php
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->with('General', array('description' => 'This section contains general settings for the web page'))
                    ->add('title', null, array('help'=>'Set the title of a web page'))
                    ->add('keywords', null, array('help'=>'Set the keywords of a web page'))
                ->end();
        }
    }
