SonataAdminBundle - The missing Symfony2 Admin Generator
========================================================

There is online documentation here:

 - http://rabaix.net/AdminBundle/html/index.html

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

Features
--------

  - Dashboard

  - List

    - Automatic sort
    - Link to associated admin (Post => User)
    - Custom templates
    - Row Action : edit, view, ...
    - Batch Action
    - Clever row visualisation : boolean values are represented with 'check picture'
    - Filter
    - Pagination

  - Edit/Create

    - Inline edition
    - Association management (create related model with + icon)
    - Group fields
    - Sortable option
    - Modal window to select model (when the list can be important)
    - Dynamic form on [one|many]-to-many association (add new element)

  - Templating

    - base templates (field, list, filter) can be overwritten
    - layout templates can be defined into the Service Container

  - Others

    - Nested Admin, ie /news/post/5/comment/list : filter and create comments only for the post with id=5
    - Contextual Breadcrumb
    - persistent parameters across an Admin
    - side menu option
    - Translated into 12 languages : DE, EN, ES, FR, IT, JA, NL, PL, PT_BR, PT_PT, RU and UK.
    - Built to be extended
    - Explain command line utility


More information
----------------

There is online documentation here:

 - http://rabaix.net/AdminBundle/html/index.html

If you want to contribute to this documentation, you need to go to the ``Resources/doc`` folder where
the reStructuredText documentation is available.
Please note the Github preview might break and hide some content.

Usage examples

 - https://github.com/sonata-project/SonataMediaBundle : a media manager bundle
 - https://github.com/sonata-project/SonataNewsBundle : a news/blog bundle
 - https://github.com/sonata-project/SonataPageBundle : a page (CMS like) bundle
 - https://github.com/sonata-project/SonataUserBundle (integration of FOSUserBundle and SonataAdminBundle)

TODO
----

  - create the ODM version
  - save filter criteria
  - export list
