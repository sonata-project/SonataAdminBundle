Form field definition
=====================

Example
-------

.. code-block:: php

    <?php
    namespace Sonta\NewsBundle\Admin;

    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Admin\Admin;

    class PostAdmin extends Admin
    {
        protected $form = array(
            'author' => array('edit' => 'list'),
            'enabled',
            'title',
            'abstract' => array('form_field_options' => array('required' => false)),
            'content',
        );

        public function configureFormFields(FormMapper $formMapper)
        {
            // equivalent to :
            $formMapper
                ->add('author', array(), array('edit' => 'list'))
                ->add('enabled')
                ->add('title')
                ->add('abtract', array(), array('required' => false))
                ->add('content')

                // you can define help messages like this
                ->setHelps(array(
                   'title' => $this->trans('help_post_title')
                ));

        }
    }

.. note::

    By default, the form framework always sets ``required=true`` for each
    field. This can be an issue for HTML5 browsers as they provide client-side
    validation.


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
    - date

If no type is set, the Admin class will use the one set in the doctrine mapping
definition.

Advanced Usage: File Management
--------------------------------

If you want to use custom types from the Form framework you must use the
``addType`` method. (The ``add`` method uses the information provided by the
model definition).

.. code-block:: php

    <?php
    namespace Sonta\NewsBundle\Admin;

    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Admin\Admin;

    class MediaAdmin extends Admin
    {
        public function configureFormFields(FormMapper $form)
        {
            $formMapper
                ->add('name', array('required' => false))
                ->add('enabled', array('required' => false))
                ->add('authorName', array('required' => false))
                ->add('cdnIsFlushable', array('required' => false))
                ->add('description', array('required' => false))
                ->add('copyright', array('required' => false))

                // add a custom type, using the native form factory
                ->addType('binaryContent', 'file', array('type' => false, 'required' => false));
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

 - ``standard``: default value, the user list is set in a select widget
 - ``list``: the user list is set in a model where you can search and select a user

In both case, you can create a new ``User`` by clicking on the "+" icon.

The last option, is ``inline`` this option embed the ``User`` form into the ``Post`` Form. This option is
great for one-to-one, or if your want to allow the user to edit the ``User`` information.

.. code-block:: php

    <?php
    namespace Sonata\NewsBundle\Admin;

    class PostAdmin extends Admin
    {
        protected $form = array(
            'author'  => array('edit' => 'list'),
        );
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

    class GalleryAdmin extends Admin
    {
        protected $form = array(
            'name',
            'galleryHasMedias' => array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable' => 'position'
            ),
        );
    }
