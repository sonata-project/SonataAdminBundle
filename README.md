AdminBundle - The missing Symfony2 Admin Generator
==================================================

*WARNING* : this is a prototype, and not a final/stable bundle.

   - code can be irrelevant
   - code might not use properly Symfony2 or Doctrine components
   - code might change with no notices.


Quick example
-------------


Defining an ``Admin`` class is pretty easy: simply define fields as properties

    class PostAdmin extends Admin
    {
        protected $form = array(
            'author' => array('edit' => 'list'),
            'enabled' => array('form_field_options' => array('required' => false)),
            'title',
            'abstract',
            'content',
            'tags'     => array('form_field_options' => array('expanded' => true)),
            'commentsCloseAt',
            'commentsEnabled' => array('form_field_options' => array('required' => false)),
        );

        protected $list = array(
            'title' => array('identifier' => true),
            'author',
            'enabled',
            'commentsEnabled',
        );

        protected $filter = array(
            'title',
            'author',
            'enabled',
        );
    }

Screenshots : http://www.dropbox.com/gallery/581816/2/BaseApplicationBundle/preview?h=59b2e8

Of course, power users will be happy as an ``Admin`` class is very flexible as all dependencies are
injected by the DIC.


More information
----------------

You need to go to the Ressources/doc folder where the reStructuredText documentation is available.
Please note the Github preview might break and hide some content.

TODO
----

  - create the ODM version
  - refactor the datagrid
  - save filter criteria
  - export list
