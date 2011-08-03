Form field definition
=====================

Example
-------

.. code-block:: php

    <?php
    namespace Sonta\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\AdminBundle\Show\ShowMapper;
    use Sonata\AdminBundle\Validator\ErrorElement;

    class PostAdmin extends Admin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('author', 'sonata_type_model', array(), array('edit' => 'list'))
                ->add('enabled')
                ->add('title')
                ->add('abtract', null, array('required' => false))
                ->add('content')

                // you can define help messages like this
                ->setHelps(array(
                   'title' => $this->trans('help_post_title')
                ));

        }

        public function validate(ErrorElement $errorElement, $object)
        {
            // conditional validation, see the related section for more information
            if ($object->getEnabled()) {
                // abstract cannot be empty when the post is enabled
                $errorElement
                    ->with('abtract')
                        ->assertNotBlank()
                        ->assertNotNull()
                    ->end()
                ;
            }
        }
    }

.. note::

    By default, the form framework always sets ``required=true`` for each
    field. This can be an issue for HTML5 browsers as they provide client-side
    validation.


Types available
---------------

    - array
    - checkbox
    - choice
    - datetime
    - decimal
    - integer
    - text
    - date
    - datetime

If no type is set, the Admin class will use the one set in the doctrine mapping
definition.

Advanced Usage: File Management
--------------------------------

If you want to use custom types from the Form framework you must use the
``addType`` method. (The ``add`` method uses the information provided by the
model definition).

.. code-block:: php

    <?php
    namespace Sonata\MediaBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\AdminBundle\Show\ShowMapper;

    class MediaAdmin extends Admin
    {
        protected function configureFormFields(FormMapper $form)
        {
            $formMapper
                ->add('name', null, array('required' => false))
                ->add('enabled', null, array('required' => false))
                ->add('authorName', null, array('required' => false))
                ->add('cdnIsFlushable', null, array('required' => false))
                ->add('description', null, array('required' => false))
                ->add('copyright', null, array('required' => false))
                ->add('binaryContent', 'file', array('required' => false));
        }
  }

.. note::

    By setting ``type=false`` in the file definition, the Form framework will
    provide an instance of ``UploadedFile`` for the ``Media::setBinaryContent``
    method. Otherwise, the full path will be provided.

Advanced Usage: Many-to-one
----------------------------

If you have many ``Post``s linked to one ``User``, then the ``Post`` form should
display a ``User`` field.

The AdminBundle provides 3 edit options:

 - ``standard``: default value, the ``User`` list is set in a select widget
 - ``list``: the ``User`` list is set in a model where you can search and select a user
 - ``inline``: embed the ``User`` form into the ``Post`` form, great for one-to-one, or if your want to allow the user to edit the ``User`` information.

With the ``standard`` and ``list`` options, you can create a new ``User`` by clicking on the "+" icon.

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\AdminBundle\Show\ShowMapper;

    class PostAdmin extends Admin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->with('General')
                    ->add('enabled', null, array('required' => false))
                    ->add('author', 'sonata_type_model', array(), array('edit' => 'list'))
                    ->add('title')
                    ->add('abstract')
                    ->add('content')
                ->end()
                ->with('Tags')
                    ->add('tags', 'sonata_type_model', array('expanded' => true))
                ->end()
                ->with('Options', array('collapsed' => true))
                    ->add('commentsCloseAt')
                    ->add('commentsEnabled', null, array('required' => false))
                    ->add('commentsDefaultStatus', 'choice', array('choices' => Comment::getStatusList()))
                ->end()
            ;
        }
    }

Advanced Usage: One-to-many
----------------------------

Let's say you have a ``Gallery`` that links to some ``Media``s with a join table
``galleryHasMedias``. You can easily add a new ``galleryHasMedias`` row by
defining one of these options:

  - ``edit``: ``inline|standard``, the inline mode allows you to add new rows
  - ``inline``: ``table|standard``, the fields are displayed into table
  - ``sortable``: if the model has a position field, you can enable a drag and
    drop sortable effect by setting ``sortable=field_name``

.. code-block:: php

    <?php
    namespace Sonata\MediaBundle\Admin;

    use Sonata\AdminBundle\Admin\Admin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;

    class GalleryAdmin extends Admin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('code')
                ->add('enabled')
                ->add('name')
                ->add('defaultFormat')
                ->add('galleryHasMedias', 'sonata_type_collection', array(), array(
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable'  => 'position'
                ))
            ;
        }
    }
