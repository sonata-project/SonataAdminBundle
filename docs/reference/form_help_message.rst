Form Help Messages and Descriptions
===================================

Help Messages
-------------

You can use `Symfony 'help' option`_ to add help messages that are rendered together with form fields.
They are generally used to show additional information so the user can complete
the form element faster and more accurately.

Example
^^^^^^^

.. code-block:: php

    // src/Admin/PostAdmin.php

    final class PostAdmin extends AbstractAdmin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->with('General')
                    ->add('title', null, [
                        'help' => 'Set the title of a web page'
                    ])
                    ->add('keywords', null, [
                        'help' => 'Set the keywords of a web page'
                    ])
                ->end()
            ;
        }
    }

.. figure:: ../images/help_message.png
   :align: center
   :alt: Example of the two form fields with help messages.

Advanced usage
^^^^^^^^^^^^^^

Since help messages can contain HTML they can be used for more advanced solutions.
See the cookbook entry :doc:`Showing image previews <../cookbook/recipe_image_previews>` for a detailed example of how to
use help messages to display an image tag.

Form Group Descriptions
-----------------------

A form group description is a block of text rendered below the group title.
These can be used to describe a section of a form. The text is not escaped,
so HTML can be used.

Example
^^^^^^^

.. code-block:: php

    // src/Admin/PostAdmin.php

    final class PostAdmin extends AbstractAdmin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->with('General', [
                    'description' => 'This section contains general settings for the web page'
                ])
                    ->add('title', null, [
                        'help' => 'Set the title of a web page'
                    ])
                    ->add('keywords', null, [
                        'help' => 'Set the keywords of a web page'
                    ])
                ->end()
            ;
        }
    }

.. _`Symfony 'help' option`: https://symfony.com/doc/4.4/reference/forms/types/form.html#help
